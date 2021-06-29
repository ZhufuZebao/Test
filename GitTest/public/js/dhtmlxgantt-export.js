/**
 * export html content within a div
 */
function exportTest(id, path)
{
    var content = $('#'+id).html();

    $.ajax({
        url: path,
        type: 'post',
        data: { "content": content },
        success: function( data, textStatus, jQxhr ){
            console.log(data);
        },
        error: function( jqXhr, textStatus, errorThrown ){
            console.log( errorThrown );
        }
    });

}

function exportTest2(elem_id, form_id)
{
    var content = $('#'+elem_id).html();
    var form    = $('#'+form_id);

    elem = document.getElementById('input-hidden-content');
    elem.value = content;
    form.submit();
}
