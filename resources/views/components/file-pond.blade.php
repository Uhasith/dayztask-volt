<div class="mt-1" x-data x-init="FilePond.registerPlugin(FilePondPluginImagePreview);
FilePond.registerPlugin(FilePondPluginFileValidateType);
FilePond.registerPlugin(FilePondPluginImageCrop);
const acceptAttr = '{{ isset($attributes['accept']) ?? '' }}';
const acceptedFileTypes = acceptAttr ? acceptAttr.split(',') : [];
FilePond.create($refs.input);
FilePond.setOptions({
    allowMultiple: {{ isset($attributes['multiple']) ? 'true' : 'false' }},
    imageCropAspectRatio: '1:1',
    maxFiles: {{ isset($attributes['max']) ? $attributes['max'] : '10' }},
    acceptedFileTypes: acceptedFileTypes,
    server: {
        process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
            @this.upload('{{ $attributes['wire:model'] }}', file, load, error, progress)
        },
        revert: (fileName, load) => {
            @this.removeUpload('{{ $attributes['wire:model'] }}', fileName, load)
        },
    },
});" wire:ignore>
    <input id="file" type="file" x-ref="input" />
</div>
