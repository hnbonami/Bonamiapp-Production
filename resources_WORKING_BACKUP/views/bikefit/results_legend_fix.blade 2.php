/* Voeg extra CSS toe voor betere leesbaarheid van de legende */
<style>
    /* Maak legende tekst zwart en vet voor betere zichtbaarheid */
    .legend-text,
    .legend-item,
    svg text,
    .chart-legend text,
    text[fill="#4FC3F7"],
    text[fill="#87CEEB"],
    text[fill*="blue"] {
        fill: #000000 !important;
        color: #000000 !important;
        font-weight: bold !important;
        font-size: 14px !important;
    }
    
    /* Specifiek voor SVG elementen */
    svg .legend text {
        fill: #000000 !important;
        font-weight: bold !important;
    }
</style>