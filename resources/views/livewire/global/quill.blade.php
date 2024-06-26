<div wire:ignore>
    <!-- Create the editor container -->
    <div id="{{ $quillId }}" class="min-h-[20vh]">
    </div>
</div>

<!-- Initialize Quill editor -->
@script
    <script>
        const quill = new Quill('#{{ $quillId }}', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{
                        'header': [1, 2, true]
                    }],
                    ['bold', 'italic', 'underline'],
                    ['link'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    [{
                        'script': 'sub'
                    }, {
                        'script': 'super'
                    }],
                    [{
                        'indent': '-1'
                    }, {
                        'indent': '+1'
                    }],
                    [{
                        'direction': 'rtl'
                    }],
                    [{
                        'color': []
                    }, {
                        'background': []
                    }],
                    [{
                        'align': []
                    }],
                    ['clean']
                ],
            },
        });

        let initialValue = $wire.$get('value');
        quill.clipboard.dangerouslyPasteHTML(initialValue);

        // Function to update Livewire value
        function updateLivewireValue() {
            let value = document.getElementsByClassName('ql-editor')[0].innerHTML;
            $wire.set('value', value);
        }

        // Debounce function
        function debounce(func, timeout = 300) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    func.apply(this, args);
                }, timeout);
            };
        }

        const debouncedUpdateLivewireValue = debounce(updateLivewireValue, 1000);

        quill.on('text-change', function() {
            debouncedUpdateLivewireValue();
        });

        // Update value on blur
        document.querySelector(`#${quill.container.id} .ql-editor`).addEventListener('blur', function() {
            updateLivewireValue();
        });
    </script>
@endscript
