$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})


function showUserList (groupId, groupName) {
    const modal = $('#userlist')
    modal.find('.modal-title').text('Benutzerliste: ' + groupName)
    $.get('ajax.php', {action: 'groups-listuser', GID: groupId}, function (res) {
        var html = []
        for (var key in res) {
            html.push('<a href="index.php?action=user&GID=' + key + '">'+res[key]+'</a>')
        }
        modal.find('.modal-body').html(html.join(', '))
        modal.modal()
    }, "json")
}


function editGrp (groupId, groupName, isAdmin) {
    const modal = $('#edit')
    // change the modal title
    modal.find('.modal-title')
    .text((groupId == 'new' ? 'Neue Gruppe' : 'Bearbeiten: ' + groupName))
    // change the form elements
    modal.find('[name="editGID"]').val(groupId)
    modal.find('[name="name"]').val(groupName)
    modal.find('[name="special[]"]').prop('checked', isAdmin)
    modal.modal()
}


function removeGrp (groupId, groupName) {
    const modal = $('#remove')
    modal.find('.modal-title').text('Gruppe ' + groupName + ' entfernen?')
    modal.find('[name="removeGID"]').val(groupId)
    modal.modal()
}