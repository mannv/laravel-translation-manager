<?php namespace Barryvdh\TranslationManager;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Barryvdh\TranslationManager\Models\Translation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tanmuhittin\LaravelGoogleTranslate\Commands\TranslateFilesCommand;

class Controller extends BaseController
{
    /** @var \Barryvdh\TranslationManager\Manager */
    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function getIndex($group = null)
    {
        $locales = $this->manager->getLocales();
        $groups = Translation::groupBy('group');
        $excludedGroups = $this->manager->getConfig('exclude_groups');
        if ($excludedGroups) {
            $groups->whereNotIn('group', $excludedGroups);
        }

        $groups = $groups->select('group')->orderBy('group')->get()->pluck('group', 'group');
        if ($groups instanceof Collection) {
            $groups = $groups->all();
        }
        $groups = ['' => 'Choose a group'] + $groups;
        $numChanged = Translation::where('group', $group)->where('status', Translation::STATUS_CHANGED)->count();

        $totalNull = Translation::where('group', $group)->whereNull('value')->count();


        $empty = \request()->get('empty');
        $condition = Translation::where(['group' => $group, 'locale' => 'en']);
        if (!empty($empty)) {
            $condition = $condition->whereNull('value');
        }
        $allTranslations = $condition->orderBy('key', 'asc')->get();
        $numTranslations = count($allTranslations);
        $translations = [];
        foreach ($allTranslations as $translation) {
            $translations[$translation->key][$translation->locale] = $translation;
        }


        $static = Translation::select(['locale', DB::raw('COUNT(id) AS total')])->groupBy('locale')->get()->toArray();


        $staticData = [];
        if (!empty($static)) {
            $staticEmpty = Translation::select([
                'locale',
                DB::raw('COUNT(id) AS total')
            ])->whereNull('value')->groupBy('locale')->get()->toArray();


            $emptyData = [];
            if (!empty($staticEmpty)) {
                foreach ($staticEmpty as $item) {
                    $emptyData[$item['locale']] = $item['total'];
                }
            }

            foreach ($static as $item) {
                $staticData[$item['locale']] = [
                    'total' => $item['total'],
                    'empty' => $emptyData[$item['locale']] ?? $item['total'],
                ];
            }
        }

        return view('translation-manager::index')
            ->with('translations', $translations)
            ->with('locales', $locales)
            ->with('groups', $groups)
            ->with('group', $group)
            ->with('numTranslations', $numTranslations)
            ->with('totalNull', $totalNull)
            ->with('numChanged', $numChanged)
            ->with('static', $staticData)
            ->with('editUrl', action('\Barryvdh\TranslationManager\Controller@postEdit', [$group]))
            ->with('deleteEnabled', $this->manager->getConfig('delete_enabled'));
    }

    public function getView($group = null)
    {
        return $this->getIndex($group);
    }

    protected function loadLocales()
    {
        //Set the default locale as the first one.
        $locales = Translation::groupBy('locale')
            ->select('locale')
            ->get()
            ->pluck('locale');

        if ($locales instanceof Collection) {
            $locales = $locales->all();
        }
        $locales = array_merge([config('app.locale')], $locales);
        return array_unique($locales);
    }

    public function postAdd($group = null)
    {
        $keys = explode("\n", request()->get('keys'));

        foreach ($keys as $key) {
            $key = trim($key);
            if ($group && $key) {
                $this->manager->missingKey('*', $group, $key);
            }
        }
        return redirect()->back();
    }

    public function postEdit($group = null)
    {
        $key = request()->get('name');
        $value = request()->get('value');
        $this->updateKeyValue($group, $key, $value);
        return array('status' => 'ok');
    }

    private function updateKeyValue($group, $key, $value)
    {
        if (!in_array($group, $this->manager->getConfig('exclude_groups'))) {

            list($locale, $key) = explode('|', $key, 2);

            $translation = Translation::firstOrNew([
                'locale' => $locale,
                'group' => $group,
                'key' => $key,
            ]);
            $translation->value = (string)$value ?: null;
            $translation->status = Translation::STATUS_CHANGED;
            $translation->save();
        }
    }


    public function postDelete($group = null)
    {
        $key = \request()->get('key');
        if (!in_array($group,
                $this->manager->getConfig('exclude_groups')) && $this->manager->getConfig('delete_enabled')) {
            Translation::where('group', $group)->where('key', $key)->delete();
            return ['status' => 'ok'];
        }
    }

    public function postImport(Request $request)
    {
        $replace = $request->get('replace', false);
        $counter = $this->manager->importTranslations($replace);

        return ['status' => 'ok', 'counter' => $counter];
    }

    public function postFind()
    {
        $numFound = $this->manager->findTranslations();

        return ['status' => 'ok', 'counter' => (int)$numFound];
    }

    public function postPublish($group = null)
    {
        $json = false;

        if ($group === '_json') {
            $json = true;
        }

        $this->manager->exportTranslations($group, $json);

        return ['status' => 'ok'];
    }

    public function postAddGroup(Request $request)
    {
        $group = str_replace(".", '', $request->input('new-group'));
        if ($group) {
            return redirect()->action('\Barryvdh\TranslationManager\Controller@getView', $group);
        } else {
            return redirect()->back();
        }
    }

    public function postAddLocale(Request $request)
    {
        $locales = $this->manager->getLocales();
        $newLocale = str_replace([], '-', trim($request->input('new-locale')));
        if (!$newLocale || in_array($newLocale, $locales)) {
            return redirect()->back();
        }
        $this->manager->addLocale($newLocale);
        return redirect()->back();
    }

    public function postRemoveLocale(Request $request)
    {
        foreach ($request->input('remove-locale', []) as $locale => $val) {
            $this->manager->removeLocale($locale);
        }
        return redirect()->back();
    }

    public function postTranslateMissing(Request $request)
    {
        ini_set('max_execution_time', 600);
        $locales = $this->manager->getLocales();
        $newLocale = str_replace([], '-', trim($request->input('new-locale')));
        if ($request->has('with-translations') && $request->has('base-locale') && in_array($request->input('base-locale'),
                $locales) && $request->has('file') && in_array($newLocale, $locales)) {
            $base_locale = $request->get('base-locale');
            $group = $request->get('file');
            $base_strings = Translation::where('group', $group)->where('locale', $base_locale)->get();
            foreach ($base_strings as $base_string) {
                $base_query = Translation::where('group', $group)->where('locale', $newLocale)->where('key',
                    $base_string->key);
                if ($base_query->exists() && $base_query->whereNotNull('value')->exists()) {
                    // Translation already exists. Skip
                    continue;
                }
                $translated_text = TranslateFilesCommand::translate($base_locale, $newLocale,
                    $base_string->value ?? $base_string->key);
                dd($translated_text);


                $key = $newLocale . '|' . $base_string->key;
                $this->updateKeyValue($group, $key, $translated_text);
            }
            return redirect()->back();
        }
        return redirect()->back();
    }
}
