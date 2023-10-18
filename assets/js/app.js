const $ = require('jquery');
global.$ = global.jQuery = $;

require( '../css/bootstrap.scss' );
require( '../css/style.scss' );
require('../css/burger.css')

require('../js/burger.js');
require('./miniatures.js');
require('./outils.js');

import 'bs5-lightbox';
//import 'leaflet';
//import "leaflet/dist/leaflet.css";

//map = L.map('implantmap').setView([51.505, -0.09], 13);
//L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
//    maxZoom: 19,
//    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
//}).addTo(map);