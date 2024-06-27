<div class="mt-0.5" x-data x-init="const toolbarOptions = [
    ['bold', 'italic', 'underline', 'strike'], // toggled buttons
    ['blockquote', 'code-block'],
    ['link'],
    [{ 'header': 1 }, { 'header': 2 }], // custom button values
    [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'list': 'check' }],
    [{ 'script': 'sub' }, { 'script': 'super' }], // superscript/subscript
    [{ 'indent': '-1' }, { 'indent': '+1' }], // outdent/indent
    [{ 'direction': 'rtl' }], // text direction
    [{ 'size': ['small', false, 'large', 'huge'] }], // custom dropdown
    [{ 'color': [] }, { 'background': [] }], // dropdown with defaults from theme
    [{ 'align': [] }],
    ['clean'] // remove formatting button
];

const quill = new Quill($refs.editor, {
    modules: {
        toolbar: toolbarOptions
    },
    theme: 'snow'
});

let initialValue = $wire.$get('{{ $attributes['wire:model'] }}');
quill.clipboard.dangerouslyPasteHTML(initialValue);

quill.on('text-change', function() {
    let value = document.getElementsByClassName('ql-editor')[0].innerHTML;
    $wire.set('{{ $attributes['wire:model'] }}', value);
});" wire:ignore>
    <div id="editor" x-ref="editor"></div>
</div>
