<div class="mt-1" x-data="{
    initFilePond() {
        FilePond.registerPlugin(FilePondPluginImagePreview);
        FilePond.registerPlugin(FilePondPluginFileValidateType);
        FilePond.registerPlugin(FilePondPluginImageCrop);
        FilePond.registerPlugin(FilePondPluginGetFile);

        const acceptAttr = '{{ isset($attributes['accept']) ? $attributes['accept'] : '' }}';
        const acceptedFileTypes = acceptAttr ? acceptAttr.split(',').map(type => type.trim()) : [];
        const filesArray = [
            @if (isset($attributes['uploads']))
                @foreach ($attributes['uploads'] as $attachment)
                    {
                        source: '{{ $attachment->getUrl() }}',
                        options: {
                            type: 'local',
                            metadata: {
                                id: '{{ $attachment->id }}',
                                old: true
                            }
                        }
                    },
                @endforeach
            @endif
        ];

        // Create FilePond instance with individual configurations
        const pond = FilePond.create($refs.input, {
            allowMultiple: {{ isset($attributes['multiple']) ? 'true' : 'false' }},
            imageCropAspectRatio: acceptAttr.includes('image') ? '1:1' : null,
            maxFiles: {{ isset($attributes['max']) ? $attributes['max'] : '10' }},
            acceptedFileTypes: acceptedFileTypes,
            files: filesArray,
            server: {
                load: (uniqueFileId, load) => {
                    fetch(uniqueFileId)
                        .then(res => res.blob())
                        .then(load);
                },
                process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                    @this.upload('{{ $attributes['wire:model'] }}', file, load, error, progress);
                },
                revert: (fileName, load) => {
                    @this.removeUpload('{{ $attributes['wire:model'] }}', fileName, load);
                }
            }
        });

        return pond;
    }
}" x-init="initFilePond()" wire:ignore>
    <input id="file" type="file" x-ref="input" />
</div>