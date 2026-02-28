$(document).ready(function() {
    $.fn.dataTable.defaults.language = {
      "lengthMenu": "Hiển thị _MENU_ mục",
      "zeroRecords": "Không tìm thấy dữ liệu",
      "info": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
      "infoEmpty": "Hiển thị 0 đến 0 của 0 mục",
      "infoFiltered": "(được lọc từ tổng số _MAX_ mục)",
      "search": "Tìm kiếm:",
      "paginate": {
          "first": "Đầu",
          "last": "Cuối",
          "next": "Tiếp",
          "previous": "Trước"
      }
    };
 
    $('#dataTable').DataTable();
 });
 