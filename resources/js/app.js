import "./bootstrap";
import "flowbite";

document.addEventListener("livewire:navigated", () => {
    console.log('Navigate');
    initFlowbite();
});
