@include('media::admin.grid.partials.content')
<script>
    $(document).ready(function () {
        $('.jsInsertImage').on('click', function (e) {
            e.preventDefault();
            var mediaId = $(this).data('id');
            var connection = queryString.connection;
            window.opener.{{$zone}}_includeMedia(mediaId + '?connection=' + connection);
            window.close();
        });
    });
</script>
</body>
</html>
