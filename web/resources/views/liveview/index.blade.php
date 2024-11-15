<x-mainlayout>
    <!--Table datatable-->
<table id="myTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>No</th>
            <th>Event Name</th>
            <th>Date</th>
            <th>Location </th>
            <th>Organizer </th>
            <th>About </th>            
            <th>Tags </th>
            <th>Action </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($events as $event)
            <tr>
                <td>{{ $event->id }}</td>
                <td>{{ $event->title }}</td>
                <td>{{ $event->date }}</td>
                <td>{{ $event->venue }}</td>
                <td>{{ $event->organizer->name }}</td>
                <td class = "description">{{ Str::limit($event->description, $limit = 50, $end = '...') }}</td>               
                <td>{{ $event->tags }}</td>
                <td>
                    <a href = "{{route('events.edit', ['event'=>$event->id])}}">
                        <button class = "btn btn-info" href = >Edit
                        <i class="fa fa-pencil" aria-hidden="true"></i>
                    </button>
                    </a>
                    <form action="{{ route('events.destroy', ['event' => $event->id]) }}" method="POST"
                        class="inline-flex no-underline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="btn btn-danger"> Delete
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</x-mainlayout>