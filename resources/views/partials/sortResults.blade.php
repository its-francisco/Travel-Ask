<script>
    function updateFilter(value) {
        const url = new URL('{{ route('search') }}', window.location.origin);
        if ('{{request('query')}}') url.searchParams.set('query', '{{request('query')}}');  
        if ('{{request('sort')}}') url.searchParams.set('sort', '{{request('sort')}}');  
        if (value) url.searchParams.set('filter', value);  
        window.location.href = url.toString(); 
    }
    function updateSort(value) {
        const url = new URL('{{ route('search') }}', window.location.origin);
        if ('{{request('query')}}') url.searchParams.set('query', '{{request('query')}}');  
        if ('{{request('filter')}}') url.searchParams.set('filter', '{{request('filter')}}');  
        if (value) url.searchParams.set('sort', value);  
        window.location.href = url.toString();
    }
</script>
<div class="filters">
    <div class="dropdown">
        <label for="filter">Filter by:</label>
        <select id="filter" name="filter" onchange="updateFilter(this.value)">
            <option value="">All</option>
            <option value="noAnswer"  {{ request('filter') == 'noAnswer' ? 'selected' : '' }}>No answers</option>
            <option value="answer" {{ request('filter') == 'answer' ? 'selected' : '' }}>Has answers</option>
            <option value="noCorrectAnswer" {{ request('filter') == 'noCorrectAnswer' ? 'selected' : '' }}>No correct answer</option>
            <option value="correctAnswer" {{ request('filter') == 'correctAnswer' ? 'selected' : '' }}>Has correct answer</option>
        </select>
    </div>
    <div class="dropdown">
        <label for="sort">Sort by:</label>
        <select id="sort" name="sort" onchange="updateSort(this.value)">
            @if ($hasQuery)
                <option value="relevance" {{request('sort') == 'relevance' ? 'selected' : '' }}>Most Relevant</option>
            @endif
            <option value="newest" {{request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
            <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Most Viewed</option>
            <option value="votes" {{ request('sort') == 'votes' ? 'selected' : '' }}>Most Up Voted</option>
            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
        </select>
    </div>
</div>