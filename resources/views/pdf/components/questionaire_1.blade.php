<div>
    <h3 class="header4">
        {{$section_data['title']}}
    </h3>
</div>
<table style="width: 100%; border-collapse: collapse;" border="1" >
    <thead>
        <th ></th>
        <th style="width: 15%; text-align:center"> Rating</th>
        @if (isset($section_data['add_remarks']) && $section_data['add_remarks'])
            <th style="width: 20%; text-align:center"> Remarks</th>
        @endif
    </thead>
    <tbody class="table-text">
        @foreach ($section_data['questions'] as $question)
            <tr>
                <td style="font-weight: normal">{{$question}}</td>
                <td></td>
                @if (isset($section_data['add_remarks']) && $section_data['add_remarks'])
                    <td></td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
