@if(!empty($testCases))
    <ol>
        @foreach($testCases as $testCase)
            <li>
                <a target="_blank"
                   href="{{route('selenium-ide-manager.test-case.show', ['id' => $testCase['id']])}}">{{$testCase['name']}}</a>
                @if($testCase['first_test_case'])
                    <span class="text-success" data-feather="check"></span>
                @else
                    <a style="cursor: pointer"
                       title="Set test case default"
                       class="test-case-item"
                       data-suite-id="{{$suite['id']}}"
                       data-id="{{$testCase['id']}}"
                    >
                                                <span class="text-primary"
                                                      data-feather="play-circle"></span>
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
@endif
