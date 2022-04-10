import { Controller } from '@hotwired/stimulus';
import L from 'leaflet';
import 'leaflet/dist/images/marker-shadow.png';
import 'leaflet/dist/images/marker-icon.png';

/**
 *
 */
export default class extends Controller {

    static targets = ['result']
    static values = {
        societe: String,
        latitude: String,
        longitude: String,
        drop: Boolean
    }

    connect() {
        let latitude = this.latitudeValue;
        let longitude = this.longitudeValue;

        if (latitude === '' || latitude === '') {
            latitude = 50.226484;
            longitude = 5.342961;
        }

        const center = [latitude, longitude];
        const map = L.map('openmap').setView(center, 16);
        const societe = this.societeValue;

        L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors',
            minZoom: 1,
            maxZoom: 20
        }).addTo(map);

        /**
         * Bug path icon
         */
        var customIcon = L.icon({
            iconUrl: require('leaflet/dist/images/marker-icon.png'),
            shadowUrl: require('leaflet/dist/images/marker-shadow.png'),
            iconRetinaUrl: require('leaflet/dist/images/marker-icon-2x.png')
        });

        var marker = L.marker(center, {title: societe, draggable: this.dropValue, icon: customIcon}).addTo(map);

        marker.addEventListener('dragend', () => {
            document.getElementById('localisation_latitude').value = marker.getLatLng().lat;
            document.getElementById('localisation_longitude').value = marker.getLatLng().lng;
        });
    }
}
