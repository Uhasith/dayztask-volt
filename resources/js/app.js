import "./bootstrap";
import "flowbite";
import flatpickr from "flatpickr"; 
import './../../vendor/power-components/livewire-powergrid/dist/powergrid';
import './../../vendor/power-components/livewire-powergrid/dist/tailwind.css';
import moment from "moment";
import timeout from '@victoryoalli/alpinejs-timeout'

// import moment from '@victoryoalli/alpinejs-moment'

import TomSelect from "tom-select";
window.TomSelect = TomSelect;
window.moment = moment();

Alpine.plugin(timeout);

document.addEventListener("livewire:navigated", () => {
    initFlowbite();
});

document.addEventListener('livewire:init', () => {
    console.log('livewire init')
    Livewire.on('play-notification-sound', (event) => {
        new Audio(event.sound).play();
    });
 });