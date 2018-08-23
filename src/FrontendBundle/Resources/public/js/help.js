$(document).ready(function(){

    $('#jstree1').jstree({
        'core' : {
            'data' : [
                { "id" : "project", "parent" : "#", "text" : "Proyecto" },
                { "id" : "add_project", "parent" : "project", "text" : "Adicionar proyecto" },
                { "id" : "del_project", "parent" : "project", "text" : "Eliminar proyecto" },
            ]
        },
        'plugins' : [ 'types', 'dnd' ],
        'types' : {
            'default' : {
                'icon' : 'fa fa-folder'
            }

        }
    });
    $('#jstree1').on("changed.jstree", function (e, data) {
        if(data.selected[0]=="project"){
            $('#add_project-content').addClass('hide');
            $('#del_project-content').addClass('hide');
            $('#project-content').removeClass('hide');
        }
        if(data.selected[0]=="add_project"){
            $('#del_project-content').addClass('hide');
            $('#project-content').addClass('hide');
            $('#add_project-content').removeClass('hide');
        }
        if(data.selected[0]=="del_project"){
            $('#project-content').addClass('hide');
            $('#add_project-content').addClass('hide');
            $('#del_project-content').removeClass('hide');
        }
    });

});