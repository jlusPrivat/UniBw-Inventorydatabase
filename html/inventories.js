$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})


function addGrp () {
    // show message, if no group is selected
    var grpselect = $('#grpselect')
    var newId = grpselect.val()
    var option = grpselect.find('option:selected')
    if (newId == 'none') {
        grpselect.tooltip('show')
        setTimeout(function () {
            grpselect.tooltip('hide')
        }, 2000)
        return
    }

    // disable the setting
    grpselect.val('none')
    option.attr('disabled', true)

    // copy the empty group
    var newRow = $('#grprow-').clone()
    newRow.attr('id', 'grprow-' + newId)
    newRow.find('input[type="checkbox"]').val(newId)
    newRow.find('.grpid').html(newId)
    newRow.find('.grpname').html(option.text())
    newRow.find('[data-toggle="tooltip"]').tooltip()
    newRow.find('.bi-trash').dblclick(function () {
        remGrp(newId)
    })
    newRow.appendTo('#grptbody')
    newRow.fadeIn()
}


function remGrp (id) {
    var elem = $('#grprow-' + id)
    elem.find('[data-toggle="tooltip"]').tooltip('dispose')
    elem.fadeOut('slow', function () {
        elem.remove()
    })
    $('#grpselect > [value="'+id+'"]').attr('disabled', false)
}


function remInv (id, name) {
    $('#remove-name').html(name)
    $('input[name="IID"]').val(id)
    $('#remove-modal').modal()
}