import "./bootstrap";
import "flowbite";
import flatpickr from "flatpickr"; 
import './../../vendor/power-components/livewire-powergrid/dist/powergrid';
import './../../vendor/power-components/livewire-powergrid/dist/tailwind.css';

import TomSelect from "tom-select";
window.TomSelect = TomSelect;

document.addEventListener("livewire:navigated", () => {
    initFlowbite();
});

document.addEventListener('livewire:init', () => {
    console.log('livewire init')
    Livewire.on('play-notification-sound', (event) => {
        new Audio(event.sound).play();
    });
 });