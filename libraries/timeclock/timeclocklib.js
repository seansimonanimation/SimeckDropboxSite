$(document).ready(function () {
    var table = $('#ShiftList').DataTable({
        "paging": false,
        "info": false,
        "searching": true,
        "order": [[0, "asc"]]
    });

    // Link the custom "Filter by artist" input to the User column
    $('#artistFilter').on('keyup', function () {
        table.search(this.value).draw();
    });
});
