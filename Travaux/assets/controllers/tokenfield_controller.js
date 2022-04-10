import {Controller} from '@hotwired/stimulus';
import Tokenfield from "tokenfield";
/*
 * This is an tokenfield controller!
 * https://github.com/KaneCohen/tokenfield
 */
export default class extends Controller {

    static values = {
        selector: String,
        url: String,
        itemName: String,
        items: String,
    }

    connect()
    {
        let element = document.querySelector(this.selectorValue);
        if (element !== null) {
            var jpn = new Tokenfield({
                el: element,
                itemName: this.itemNameValue,
                filterSetItems: false,
                remote: {
                    url: this.urlValue,
                }
            });

            if (this.itemsValue) {
                try {
                    const obj = JSON.parse(this.itemsValue);
                    jpn.setItems(obj);
                } catch (e) {
                    console.error("Parsing error:", e);
                }
            }
        }
    }
}
