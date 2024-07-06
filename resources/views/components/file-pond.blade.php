<div class="mt-1" x-data x-init="FilePond.registerPlugin(FilePondPluginImagePreview);
FilePond.registerPlugin(FilePondPluginFileValidateType);
FilePond.registerPlugin(FilePondPluginImageCrop);
FilePond.registerPlugin(FilePondPluginGetFile);
const acceptAttr = '{{ isset($attributes['accept']) ?? '' }}';
const acceptedFileTypes = acceptAttr ? acceptAttr.split(',') : [];
const filesArray = [
    @if (isset($attributes['uploads'])) @foreach ($attributes['uploads'] as $attachment) {
        source: '{{ $attachment->getUrl() }}',
        options: {
            type: 'local',
            metadata: {
                id: '{{ $attachment->id }}',
                old: true
            }
        }
    }, @endforeach @endif
];
FilePond.setOptions({
    allowMultiple: {{ isset($attributes['multiple']) ? 'true' : 'false' }},
    imageCropAspectRatio: '1:1',
    maxFiles: {{ isset($attributes['max']) ? $attributes['max'] : '10' }},
    acceptedFileTypes: acceptedFileTypes,
    server: {
        load: (uniqueFileId, load) => {
            fetch(uniqueFileId)
                .then(res => res.blob())
                .then(load);
        },
        process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
            @this.upload('{{ $attributes['wire:model'] }}', file, load, error, progress)
        },
        revert: (fileName, load) => {
            @this.removeUpload('{{ $attributes['wire:model'] }}', fileName, load)
        },
        onactivatefile: (file) => {
            console.log('File activated:', file);
            // Add any additional logic you want to perform when a file is clicked or tapped
        }
    },
});
FilePond.create($refs.input, {
    files: filesArray,
    server: {
        load: (uniqueFileId, load) => {
            fetch(uniqueFileId)
                .then(res => res.blob())
                .then(load);
        },
        process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
            @this.upload('{{ $attributes['wire:model'] }}', file, load, error, progress)
        },
        revert: (fileName, load) => {
            @this.removeUpload('{{ $attributes['wire:model'] }}', fileName, load)
        },
    }
});" wire:ignore>
    <input id="file" type="file" x-ref="input" />
</div>

@script
    <script>
        document.addEventListener('FilePond:removefile', (e) => {
            const fileId = e.detail.file.getMetadata('id');
            const old = e.detail.file.getMetadata('old');

            if (old !== undefined && old !== null && old === true) {
                $wire.dispatch('remove-upload', {
                    params: {
                        id: fileId
                    }
                });
            }

        });

        document.addEventListener('FilePond:activatefile', (e) => {
            console.log(e.detail.file.getMetadata('id'));
        });
    </script>
@endscript
