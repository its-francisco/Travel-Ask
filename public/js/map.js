
// display the map and get the coordinates of the location
async function map(){
    const map = L.map('map', {
        zoomControl: false,       // Disable zoom controls
        dragging: true,          // enable dragging
        scrollWheelZoom: false,   // Disable zooming with the scroll wheel
        doubleClickZoom: false,   // Disable zooming with double-click
        touchZoom: false          // Disable touch-based zooming
    }).setView([0, 0], 1.5);

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
    throw new Error('Place not found');
}
}
    async function fetchData(){
    const response = await fetch('/api/countries/trending');
    return (await response.json());
}
    const places = await fetchData();

    places.forEach(place => {
    getCoordinates(place.name)
    .then(coords => {
    const marker = L.marker(coords).addTo(map);
    marker.bindPopup(`<a href="/countries/${place.id}" target="_blank">${place.name}</a>`);
})
    .catch(err => console.error(err));
});

}
map();
