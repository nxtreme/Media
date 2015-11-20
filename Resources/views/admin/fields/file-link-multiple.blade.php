<div class="form-group" data-image-zone="{{ $zone }}">
    <style>
        .form-group .images-wrapper {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
        }
        figure.jsThumbnailImageWrapper:not([hidden]) {
            position: relative;
            display: inline-block;
            background-color: #fff;
            border: 1px solid #eee;
            padding: 3px;
            border-radius: 3px;
            margin-top: 20px;
            margin-right: 15px;
        }
        figure.jsThumbnailImageWrapper i {
            position: absolute;
            top:-10px;
            right:-10px;
            color: #f56954;
            font-size: 2em;
            background: white;
            border-radius: 20px;
            height: 25px;
        }
    </style>
    <script>
        function {{ $zone }}_includeMedia(mediaId) {
            $.ajax({
                type: 'POST',
                url: '{{ route('api.media.thumbnail_path') }}',
                data: {
                    'mediaId': mediaId,
                    '_token': '{{ csrf_token() }}',
                    'entityClass': '{{ $entityClass }}',
                    'entityId': '{{ isset($entityId) ? $entityId : null }}',
                    'zone': '{{ $zone }}'
                },
                success: function(data) {
                    console.log(data);
                    var randID = Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 4) + Math.round(Math.random()*10000);
                    $('#{{ sprintf("media__%s__path", $zone) }}').val(data.result.path);
                    var html = '<figure class="jsThumbnailImageWrapper">' +
                            '<img src="' + data.result.path + '" alt=""/>' +
                            '<a class="jsRemoveImage" href="#" data-id="' + mediaId + '" data-image-zone="{{ $zone }}">' +
                                '<i class="fa fa-times-circle"></i>' +
                            '</a>' +
                            '<input type="hidden" name="media[{{ $zone }}][images][' + randID + '][id]" value="' + mediaId + '">' +
                            '<input type="hidden" name="media[{{ $zone }}][images][' + randID + '][entity_class]" value="{{ $entityClass }}">' +
                            '<input type="hidden" name="media[{{ $zone }}][images][' + randID + '][entity_id]" value="{{ isset($entityId) ? $entityId : null }}">' +
                            '<input type="hidden" name="media[{{ $zone }}][images][' + randID + '][path]" value="' + data.result.path + '">' +
                            '</figure>';
                    $('[data-image-zone="{{ $zone }}"] .images-wrapper').append(html).fadeIn();
                }
            });
        }
    </script>
    {!! Form::label($zone, ucfirst($zone) . ':') !!}
    <div class="clearfix"></div>

    <?php $url = route('media.grid.select', ['zone' => $zone]) ?>
    <a class="btn btn-primary" onclick="window.open('{!! $url !!}', '_blank', 'menubar=no,status=no,toolbar=no,scrollbars=yes,height=500,width=1000');"><i class="fa fa-upload"></i>
        {{ trans('media::media.Browse') }}
    </a>

    <div class="clearfix"></div>

    <div class="images-wrapper">
    @foreach ($images as $image)
        @if (isset($image->path) === true)
            <figure class="jsThumbnailImageWrapper">
                <img src="{{ Imagy::getThumbnail($image->path, 'mediumThumb') }}" alt=""/>
                <a class="jsRemoveLink" href="#" data-id="{{ $image->id }}" data-image-zone="{{ $zone }}">
                    <i class="fa fa-times-circle"></i>
                </a>
            </figure>
        @endif
    @endforeach
    </div>
</div>
<script>
    $( document ).ready(function() {
        // Remove images that are not yet linked to the entity
        $('body').on('click', '.jsRemoveImage', function(e) {
            e.preventDefault();
            var pictureWrapper = $(this).parent();
            pictureWrapper.fadeOut().remove();
        });
        // Remove images that are linked to the entity
        $('.jsRemoveLink').on('click', function(e) {
            e.preventDefault();
            var fileId = $(this).data('id'),
                pictureWrapper = $(this).parent(),
                $fileCount = $('.jsFileCount');

            $.ajax({
                type: 'POST',
                url: '{{ route('api.media.unlink-multi') }}',
                data: {
                    'imageableId': '{{ $entityId }}',
                    'fileId': fileId,
                    'zone': '{{ $zone }}',
                    '_token': '{{ csrf_token() }}'
                },
                success: function(data) {
                    if (data.error === false) {
                        pictureWrapper.fadeOut().remove();
                        if ($fileCount.length > 0) {
                            var count = parseInt($fileCount.text());
                            $fileCount.text(count - 1);
                        }
                    } else {
                        pictureWrapper.append(data.message);
                    }
                }
            });
        });
    });
</script>
