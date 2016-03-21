<div class="form-group" data-image-zone="{{ $zone }}">
    <style>
        .form-group .images-wrapper {
            display: flex;
            flex-direction: row;
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
            $('#{{ sprintf("media__%s__id", $zone) }}').val(mediaId);
            $('#{{ sprintf("media__%s__entity_class", $zone) }}').val('{{ $entityClass }}');
            $('#{{ sprintf("media__%s__entity_id", $zone) }}').val('{{ isset($entityId) ? $entityId : null }}');

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
                    $('#{{ sprintf("media__%s__path", $zone) }}').val(data.result.path);
                    var html = '<figure class="jsThumbnailImageWrapper">' +
                        '<img src="' + data.result.path + '" alt=""/>' +
                        '<a class="jsRemoveImage" href="#" data-id="' + data.result.imageableId + '" data-image-zone="{{ $zone }}">' +
                            '<i class="fa fa-times-circle"></i>' +
                        '</a>' +
                        '</figure>';
                    $('[data-image-zone="{{ $zone }}"] .images-wrapper').append(html).fadeIn();
                }
            });
        }
    </script>
    {!! Form::label($zone, ucfirst($zone) . ':') !!}
    <div class="clearfix"></div>

    <?php $url = route('media.grid.select', ['zone' => $zone]) ?>
    <a class="btn btn-primary" onclick="window.open('{!! $url . '?connection=' . $connection !!}', '_blank', 'menubar=no,status=no,toolbar=no,scrollbars=yes,height=500,width=1000');"><i class="fa fa-upload"></i>
        {{ trans('media::media.Browse') }}
    </a>

    {!! Form::hidden(sprintf('media[%s][id]', $zone), null, ['id' => sprintf('media__%s__id', $zone)]) !!}
    {!! Form::hidden(sprintf('media[%s][entity_class]', $zone), null, ['id' => sprintf('media__%s__entity_class', $zone)]) !!}
    {!! Form::hidden(sprintf('media[%s][entity_id]', $zone), null, ['id' => sprintf('media__%s__entity_id', $zone)]) !!}
    {!! Form::hidden(sprintf('media[%s][path]', $zone), null, ['id' => sprintf('media__%s__path', $zone)]) !!}

    <div class="clearfix"></div>

    <div class="images-wrapper">
    <figure class="jsThumbnailImageWrapper">
        <!-- <?php $zone ?> -->
        <?php if (isset(${$zone}->path)): ?>
            <img src="{{ Imagy::getThumbnail(${$zone}->path, 'mediumThumb') }}" alt=""/>
            <a class="jsRemoveLink" href="#" data-id="{{ ${$zone}->pivot->id }}" data-image-zone="{{$zone}}">
                <i class="fa fa-times-circle"></i>
            </a>
        <?php endif; ?>
    </figure>
    </div>
</div>
<script>
    $( document ).ready(function() {
        // Remove images that are not yet linked to the entity
        $('body').on('click', '.jsRemoveImage', function(e) {
            e.preventDefault();

            // Clear the hidden input fields
            var zone = $(this).data('image-zone');
            $('#media__' + zone + '__id').val('');
            $('#media__' + zone + '__entity_class').val('');
            $('#media__' + zone + '__entity_id').val('');
            $('#media__' + zone + '__path').val('');

            // Remove the thumbnail image
            var pictureWrapper = $(this).parent();
            pictureWrapper.fadeOut().remove();
        });
        // Remove images that are linked to the entity
        $('[data-image-zone="{{$zone}}"]').on('click',  '.jsRemoveLink', function(e) {
            e.preventDefault();
            var imageableId = $(this).data('id');
            var me = this;
            $.ajax({
                type: 'POST',
                url: '{{ route('api.media.unlink') }}',
                data: {
                    'imageableId': imageableId,
                    '_token': '{{ csrf_token() }}'
                },
                success: function(data) {
                    if (data.error === false) {
                        $(me).parents('.jsThumbnailImageWrapper').fadeOut().html('');
                    } else {
                        $(me).parents('.jsThumbnailImageWrapper').append(data.message);
                    }
                }
            });
        });
    });
</script>
