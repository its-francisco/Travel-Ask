@extends('layouts.app')
@section('title', $name)
@section('content')



    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map {
            height: 300px;
            width: 100%;
            z-index: 1;
        }
    </style>


    <div id="map"></div>
    <script>
        const map = L.map('map', {
            zoomControl: false,       // Disable zoom controls
            dragging: false,          // Disable dragging
            scrollWheelZoom: false,   // Disable zooming with the scroll wheel
            doubleClickZoom: false,   // Disable zooming with double-click
            touchZoom: false          // Disable touch-based zooming
        }).setView([0, 0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        async function getCoordinates(placeName) {
            const url = `https://nominatim.openstreetmap.org/search?` +
                        `q=${encodeURIComponent(placeName)}&` +
                        `format=json&polygon_geojson=1&addressdetails=1`;
            const response = await fetch(url);
            const data = await response.json();

            if (data && data.length > 0) {
                const { lat, lon } = data[0];
                return [parseFloat(lat), parseFloat(lon)];
            } else {
                throw new Error('Country not found');
            }
        }
        // the openstreetmap doesn't allow search by name of place, therefore, we need to ask another API
        getCoordinates("{{$name}}")
            .then(coords => {
                map.setView(coords, 4);  // 4 is the zoom
                L.marker(coords).addTo(map).bindPopup("{{$name}}");
            })
            .catch(err => console.error(err));
    </script>

<section class="container-big">
    <div class="place-info">
        <h1 class="name">{{ $name }}</h1>

        <p class="description">{{ $description }}</p>

        @if ($city)
            @can('create', \App\Models\Event::class)
        <details class="add-event">
            <summary><p>New event</p> <span class="material-symbols-outlined">add</span></summary>
            <form action="{{ route('events.store', ['id'=>$id]) }}" method="POST">
                @csrf
                <label for="name">Event Name: <abbr class="requiredField" title="mandatory field">*</abbr></label>
                <input type="text" id="name" name="name" placeholder="Enter event name" maxlength="255" required>
                <label for="description">Event Description: <abbr class="requiredField" title="mandatory field">*</abbr></label>
                <textarea id="description" name="description" placeholder="Enter event description" rows="4" cols="50" required></textarea>
                <label for="startdatetime">Select start Date and Time: <abbr class="requiredField" title="mandatory field">*</abbr></label>
                <input type="datetime-local" id="startdatetime" name="start_date" 
                       value="{{  now()->format('Y-m-d\TH:i') }}"
                       min="{{ now()->format('Y-m-d\TH:i') }}" required>
                <label for="enddatetime">Select end Date and Time: <abbr class="requiredField" title="mandatory field">*</abbr></label>
                <input type="datetime-local" id="enddatetime" name="end_date" 
                       value="{{  now()->format('Y-m-d\TH:i')}}"
                       min="{{ now()->format('Y-m-d\TH:i') }}" required>
                <button type="submit">Submit</button>
            </form>
          </details>
            @endcan
            <script src="{{url('js/calendar.min.js')}}" defer></script>
            <div class="event-section">
                    <div class="calendar-container">
                    <header class="calendar-header">
                        <p class="calendar-current-date"></p>
                        <div class="calendar-navigation">
                            <span id="calendar-prev" 
                                class="material-symbols-outlined" title="Previous Month">
                                chevron_left
                            </span>
                            <span id="calendar-next" title="Next Month"
                                class="material-symbols-outlined">
                                chevron_right
                            </span>
                        </div>
                    </header>

                    <div class="calendar-body">
                        <ul class="calendar-weekdays">
                            <li>Sun</li>
                            <li>Mon</li>
                            <li>Tue</li>
                            <li>Wed</li>
                            <li>Thu</li>
                            <li>Fri</li>
                            <li>Sat</li>
                        </ul>
                        <ul class="calendar-dates"></ul>
                    </div>
                </div>
                <div class="events"></div>
            </div>
            
        @endif
    </div>

    <h2>Questions</h2>

    @if (count($questions) == 0)
        <p>No questions found.</p>
    @else
        <ul id="results" class="container">
        @each('partials.questionList', $questions, 'question')
        </ul>
    @endif
    
</section>



@endsection