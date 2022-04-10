import { Controller } from '@hotwired/stimulus';
import lightGallery from 'lightgallery';
// Plugins
import lgThumbnail from 'lightgallery/plugins/thumbnail'
import lgZoom from 'lightgallery/plugins/zoom'

/*
 * Gallery controller!
 * https://github.com/sachinchoolur/lightGallery
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    connect() {
        lightGallery(document.getElementById('lightgallery'), {
            plugins: [lgZoom, lgThumbnail],
            speed: 500,
        });
    }
}
