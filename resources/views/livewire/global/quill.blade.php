<div wire:ignore>
    <!-- Create the editor container -->
    <div id="{{ $quillId }}"
        class="min-h-[20vh]">
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

        quill.on('text-change', function() {
            console.log('call');
            let value = document.getElementsByClassName('ql-editor')[0].innerHTML;
            @this.set('value', value)
        });
    </script>
@endscript

