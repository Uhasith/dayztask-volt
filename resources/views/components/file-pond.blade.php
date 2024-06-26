<div class="mt-1" x-data x-init="FilePond.registerPlugin(FilePondPluginImagePreview);
FilePond.registerPlugin(FilePondPluginImageCrop);
FilePond.create($refs.input);
FilePond.setOptions({
    allowMultiple: {{ isset($attributes['multiple']) ? 'true' : 'false' }},
    imageCropAspectRatio: '1:1',
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
