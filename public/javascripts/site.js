$('.item').click(function () {
    var isDoing = confirm('정말 이 강좌를 취소하시겠습니까?');
    if (isDoing) {
        var lecture_id = $(this).data('lectureid');

        $('#inform').html('<form action="/remove/lecture" name="vote" method="post" style="display:none;"><input type="hidden" name="lecture_id" value="' + lecture_id + '"/></form>');
        document.forms['vote'].submit();
    }
});

$('.adminitem').click(function () {
    var isDoing = confirm('정말 이 관리자를 재명하시겠습니까?');
    if (isDoing) {
        var admin_id = $(this).data('adminid');

        $('#inform').html('<form action="/manager/admins/delete" name="vote" method="post" style="display:none;"><input type="hidden" name="admin_id" value="' + admin_id + '"/></form>');
        document.forms['vote'].submit();
    }
});
