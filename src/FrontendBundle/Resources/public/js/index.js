var managerApisprojects = null;
var managerUrls = null;
var btnActionModal = null;

var validatorGeneralTab;
var validatorUrlsTab;
var rowSelect="";
$(document).ready(function () {

    var projectsTable = $('#table-projects').DataTable( {
        select: true,
        columnDefs: [
            {
                "targets": [0],
                "visible": false,
                "searchable": false
            }
        ]
    });

    initValidator();

    initDetails(projectsTable);
    managerApisprojects = initAddApiproject();
    managerUrls = initAddUrl();
    initConfirmDelete();
    initNewProject();
    initEditProject();

    $(".linkExternal").click(function() {
        var url = $(this).attr("href");
        window.open(url, '_blank');
        return false;
    });
    shortcut.add("Alt+i",function() {
        $('.new-project').click();
    });
    shortcut.add("Alt+c",function() {
        if(rowSelect!="")
            $('#mod-'+rowSelect[0]).click();
    });
    shortcut.add("Delete",function() {
        if(rowSelect!="")
            $('#del-'+rowSelect[0]).click();
    });
    shortcut.add("Alt+h",function() {
        $('#help').modal('show');
    });
    projectsTable.on( 'select', function ( e, dt, type, indexes ) {
        if ( type === 'row' ) {
            var data = projectsTable.rows( indexes ).data()[0];
            rowSelect=data;
        }
    });
    projectsTable.on( 'deselect', function ( e, dt, type, indexes ) {
        if ( type === 'row' ) {
            rowSelect="";
        }
    });
});

function initValidator(){
    var tab1 = $('#tab-1');
    var formTab1 = tab1.find('form');
    validatorGeneralTab = formTab1.validate({
        errorElement: 'span', //default input error message container
        errorClass: 'has-error', // default input error message class
        ignore: "",
        errorLabelContainer: tab1.find(".error-container"),
        rules: {
            'frontendbundle_datproject[name]': {
                required: true
            },
            'frontendbundle_datproject[applicationtype]': {
                required: true
            }
        },
        invalidHandler: function (event, validator) {},
        highlight: function (element, clsError) { // hightlight error inputs
            element = $(element);
            element.parent().addClass(clsError);
        },
        unhighlight: function (element, clsError) { // revert the change done by hightlight
            element = $(element);
            element.parent().removeClass(clsError);
        }
    });
    $('#frontendbundle_datproject_applicationtype').change(function(){
        validatorGeneralTab.form();
    });

    var tab2 = $('#tab-2');
    var formTab2 = tab2.find('form');
    validatorUrlsTab = formTab2.validate({
        errorElement: 'span', //default input error message container
        errorClass: 'has-error', // default input error message class
        ignore: "",
        errorLabelContainer: tab1.find(".error-container"),
        invalidHandler: function (event, validator) {},
        highlight: function (element, clsError) { // hightlight error inputs
            element = $(element);
            element.parent().addClass(clsError);
        },
        unhighlight: function (element, clsError) { // revert the change done by hightlight
            element = $(element);
            element.parent().removeClass(clsError);
        }
    });
}

function formsValid(){
    var msgInvalid = 'Existen campos invalidos';
    if(!validatorGeneralTab.form()){
        $('[href=#tab-1]').click();
        hds.msg.show(3, msgInvalid);
        return false;
    }
    if(!validatorUrlsTab.form()){
        $('[href=#tab-2]').click();
        hds.msg.show(3, msgInvalid);
        return false;
    }

    return true;
}

function initDetails(projectsTable){
    var animationIn = 'slideInDown';
    var animationOut = 'slideOutDown';
    var self=this;
    projectsTable.on( 'select', function ( e, dt, type, indexes ) {
        if ( type === 'row' ) {
            var data = projectsTable.rows( indexes ).data()[0];
            rowSelect=data;
            var detailId = '#' + 'project_id_' + data[0];

            projectDetailActive.addClass(animationOut);
            projectDetailActive.addClass('animated');
            setTimeout(function(){
                projectDetailActive.addClass('hide');
                projectDetailActive.removeClass(animationOut);
                projectDetailActive.removeClass('animated');

                projectDetailActive = $(detailId);
                projectDetailActive.removeClass('hide');
                projectDetailActive.addClass(animationIn);
                projectDetailActive.addClass('animated');
                setTimeout(function(){
                    projectDetailActive.removeClass(animationIn);
                    projectDetailActive.removeClass('animated');
                }, 800);
            }, 100);
        }
    } );

    var projectDetails = $('.details-project');
    projectDetails.addClass('hide');
    var projectDetailActive = null;
    if(projectDetails.length){
        projectDetailActive = projectDetails.first();
        projectDetailActive.removeClass('hide');

        var rows = [0];
        projectsTable.rows( rows).select();
    }
}

function initAddApiproject(){
    var addApiprojectLink = $('<a href="#" class="icon-add-link">' +
        '<i class="fa fa-plus-circle"></i>' +
        '</a>');
    $('#addapiproject').append(addApiprojectLink);

    var initproto = false;

    function addApiproject(apikey, api){
        addApiprojectForm(collectionApiproject, newApiprojectLi, apikey, api);
    }

    function addApiprojectForm(collectionHolder, newLinkLi, apikeyValue, apiValue) {
        // Get the data-prototype explained earlier
        var prototype = collectionHolder.data('prototype');

        if(!initproto){
            var htmlProto = $(prototype);
            var inputGroup = $(htmlProto.children()[0]);
            inputGroup.attr('class', 'input-group gen-key');
            inputGroup.attr('style', 'width: 96%;margin-bottom: 2px;');
            inputGroup.append('<span class="input-group-btn"> <button class="btn btn-primary fa fa-rotate-left" type="button" onclick="generateKey(this)"></button> </span>');

            var htmlAux = '<div></div>';
            var aux = $(htmlAux);
            aux.append(htmlProto);

            prototype = aux.html();
            collectionHolder.data('prototype', prototype);

            initproto = true;
        }

        // get the new index
        var index = collectionHolder.find('li').length - 1;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolder.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        var newFormLi = $('<li class="col-xs-12  p-0 m-b-5 p-relative p-l-10"></li>').append(newForm);
        newLinkLi.before(newFormLi);

        var inputApiKey = newFormLi.find('input');
        if(apikeyValue){
            inputApiKey.val(apikeyValue);
        }
        if(apiValue){
            var inputApi = newFormLi.find('select');
            inputApi.val(apiValue);
        }
        inputApiKey.prop('disabled', true);

        var temp = collectionApiproject.find('li').length - 2;
        $('#frontendbundle_datproject_apisprojects_'+temp+'_api').val('').chosen().change(function(){
            updateApisVisibility();

            var btn = newFormLi.find('.btn-primary');
            generateKey(btn[0]);
        });
        $('#frontendbundle_datproject_apisprojects_'+temp+'_api_chosen.chosen-container-single').width( '100%' );

        // add a delete link to the new form
        addApiprojectFormDeleteLink(newFormLi, index);
        newFormLi.attr('indexForm', index+'');

        updateApisVisibility();
    }

    function updateApisVisibility(){
       var allValues = {};

        var selects = $('#modal-new-project #form-apisprojects').find('select');
        selects.each(function(){
            var select = $(this);
            var val = select.val();
            if(val){
                allValues[val] = true;
            }
        });

        selects.each(function () {
            var select = $(this);
            select.find('option').each(function () {
                var option = $(this);
                var val = option.val();
                if(allValues[val]){
                    option.addClass('hide');
                }
                else{
                    option.removeClass('hide');
                }
            });

            select.chosen().trigger("chosen:updated");
        });
    }

    function addApiprojectFormDeleteLink(mediaFormLi, index) {
        index += 1;
        var inde;
        /*if(index>=10)
         inde = $('<h4 style="padding: 40px 9px 15px;">' + index + '</h4>');
         else*/
        inde = $('<h4>' + index + '</h4>');

        var display = (index == 1) ? ('display: none') : ('');

        var removeFormA = $('<a href="#" class="icon-minus-link z-index-1000" style="'+ display +'">' +
            '<i class="fa fa-minus-circle"></i>' +
            '</a>');
        mediaFormLi.append(removeFormA);
        mediaFormLi.append(inde);

        removeFormA.on('click', function (e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // remove the li for the tag form
            mediaFormLi.remove();
            updateApiproject();
            updateApisVisibility();
        });
    }

    function clearApisprojects(){
        collectionApiproject.find('li').each(function () {
            var urlCmp = $(this);
            if(!urlCmp.hasClass('flag')){
                urlCmp.remove();
            }
        });
    }

    function updateApiproject(){
        collectionApiproject.find('li').each(function(index){
            var mediaFormLi = $(this);
            if(mediaFormLi.attr('indexForm')){
                var oldIndex = mediaFormLi.attr('indexForm');

                var divContainer = $(mediaFormLi.children()[0]);

                var label = divContainer.find('label');
                var input = divContainer.find('input');
                var select = divContainer.find('select');
                var h4 = mediaFormLi.find('h4');
                var newIndex = index+'';

                mediaFormLi.attr('indexForm', newIndex);
                divContainer.attr('id', divContainer.attr('id').replace(oldIndex, newIndex));
                label.attr('for', label.attr('for').replace(oldIndex, newIndex));
                input.attr('id', input.attr('id').replace(oldIndex, newIndex));
                input.attr('name', input.attr('name').replace(oldIndex, newIndex));
                select.attr('id', select.attr('id').replace(oldIndex, newIndex));
                select.attr('name', select.attr('name').replace(oldIndex, newIndex));
                h4.html(index+1+'');
            }
        });
    }

    var newApiprojectLi = $('<li class="flag"></li>');

    // Get the ul that holds the collection of urls
    var collectionApiproject = $('ul.apisprojects');

    // add a delete url link to all of the existing tag form li elements
    collectionApiproject.find('li').each(function () {
        addApiprojectFormDeleteLink($(this));
    });

    // add the "add a url" anchor and li to the urls ul
    collectionApiproject.append(newApiprojectLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionApiproject.data('index', collectionApiproject.find('li').length - 1);

    addApiprojectLink.on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new tag form (see next code block)
        addApiproject();
        var temp = collectionApiproject.find('li').length - 2;
        $('#frontendbundle_datproject_apisprojects_'+temp+'_key').focus();
        /*$('frontendbundle_datproject_urls_'+temp+'_dir').rules( "add", {
         required: false
         });*/
    });
    addApiproject();
    /*var temp = collectionApiproject.find('li').length - 2;
    $('#frontendbundle_datproject_apisprojects_'+temp+'_api_chosen.chosen-container-single').width( '100%' );*/

    return {
        addApiproject : function(apikey, api){
            addApiproject(apikey, api);
        },
        clearApisprojects:function(){
            clearApisprojects();
        }
    }
}

function initAddUrl(){
    var addUrlLink = $('<a href="#" class="icon-add-link">' +
        '<i class="fa fa-plus-circle"></i>' +
        '</a>');
    $('#addurl').append(addUrlLink);

    function addUrl(url){
        addUrlForm(collectionUrl, newUrlLi, url);
    }

    function addUrlForm(collectionHolder, newLinkLi, urlValue) {
        // Get the data-prototype explained earlier
        var prototype = collectionHolder.data('prototype');

        // get the new index
        var index = collectionHolder.find('li').length - 1;

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, index);

        // increase the index with one for the next item
        collectionHolder.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        var newFormLi = $('<li class="col-xs-12 p-0 m-b-5 p-relative p-l-10"></li>').append(newForm);
        newLinkLi.before(newFormLi);

        var inputUrl = newFormLi.find('input');
        if(urlValue){
            inputUrl.val(urlValue);
        }

        inputUrl.rules( "add", {url: true, required:false});


        // add a delete link to the new form
        addUrlFormDeleteLink(newFormLi, index);
        newFormLi.attr('indexForm', index+'');
    }

    function addUrlFormDeleteLink(mediaFormLi, index) {
        index += 1;
        var inde;
        /*if(index>=10)
         inde = $('<h4 style="padding: 40px 9px 15px;">' + index + '</h4>');
         else*/
        inde = $('<h4>' + index + '</h4>');

        var display = (index == 1) ? ('display: none') : ('');

        var removeFormA = $('<a href="#" class="icon-minus-link z-index-1000" style="'+ display +'">' +
            '<i class="fa fa-minus-circle"></i>' +
            '</a>');
        mediaFormLi.append(removeFormA);
        mediaFormLi.append(inde);

        removeFormA.on('click', function (e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // remove the li for the tag form
            mediaFormLi.remove();
            updateUrls();
        });
    }

    function clearUrls(){
        collectionUrl.find('li').each(function () {
            var urlCmp = $(this);
            if(!urlCmp.hasClass('flag')){
                urlCmp.remove();
            }
        });
    }

    function updateUrls(){
        collectionUrl.find('li').each(function(index){
            var mediaFormLi = $(this);
            if(mediaFormLi.attr('indexForm')){
                var oldIndex = mediaFormLi.attr('indexForm');

                var divContainer = $(mediaFormLi.children()[0]);
                var label = divContainer.find('label');
                var input = divContainer.find('input');
                var h4 = mediaFormLi.find('h4');
                var newIndex = index+'';

                mediaFormLi.attr('indexForm', newIndex);
                divContainer.attr('id', divContainer.attr('id').replace(oldIndex, newIndex));
                label.attr('for', label.attr('for').replace(oldIndex, newIndex));
                input.attr('id', input.attr('id').replace(oldIndex, newIndex));
                input.attr('name', input.attr('name').replace(oldIndex, newIndex));
                h4.html(index+1+'');
            }
        });
    }

    var newUrlLi = $('<li class="flag"></li>');

    // Get the ul that holds the collection of urls
    var collectionUrl = $('ul.urls');

    // add a delete url link to all of the existing tag form li elements
    collectionUrl.find('li').each(function () {
        addUrlFormDeleteLink($(this));
    });

    // add the "add a url" anchor and li to the urls ul
    collectionUrl.append(newUrlLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    collectionUrl.data('index', collectionUrl.find('li').length - 1);

    addUrlLink.on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new tag form (see next code block)
        addUrl();
        var temp = collectionUrl.find('li').length - 2;
        $('#frontendbundle_datproject_urls_'+temp+'_dir').focus();
        /*$('frontendbundle_datproject_urls_'+temp+'_dir').rules( "add", {
         required: false
         });*/
    });

    addUrl();

    return {
        addUrl : function(url){
            addUrl(url);
        },
        clearUrls:function(){
            clearUrls();
        }
    }
}

function initConfirmDelete(){
    var confirmLenguaje = $('#confirm-lenguaje');
    $('.delete-project').on('click', function(){
        var link = $(this);
        $.confirm({
            title: confirmLenguaje.data('title'),
            content: confirmLenguaje.data('content'),
            confirmButton: confirmLenguaje.data('confirmbutton'),
            cancelButton: confirmLenguaje.data('cancelbutton'),
            backgroundDismiss: false,
            confirm: function(){
                window.location.href=link.data('href');
            },
            cancel: function(){}
        });
    });
}

function initNewProject(){
    $('.new-project').on('click', function(){
        btnActionModal = $(this);

        var modal = $('#modal-new-project');

        var title = modal.find('.modal-title');
        title.html(title.data('title-new'));

        managerApisprojects.clearApisprojects();
        managerUrls.clearUrls();

        managerApisprojects.addApiproject();
        managerUrls.addUrl();

        modal.find('input.form-control').each(function(){
            var input = $(this);
            input.val('');
        });
        modal.find('textarea').each(function(){
            var input = $(this);
            input.val('');
        });
        modal.find('select').each(function(){
            var input = $(this);
            input.val('').chosen().trigger("chosen:updated");
        });
    });
}

function initEditProject(){
    $('.edit-project').on('click', function(){
        var link = $(this);
        btnActionModal = link;

        var modal = $('#modal-new-project');

        var title = modal.find('.modal-title');
        title.html(title.data('title-edit'));

        var id = 'project_id_data_' + link.data('idproject');
        var data = JSON.parse($('#'+id).html());

        var urls = 0;
        var apiskey = 0;
        for (var idData in data) {
            if (data.hasOwnProperty(idData)) {
                if (idData.indexOf('apikey') != -1) {
                    apiskey++;
                }
                else if (idData.indexOf('urls')  != -1) {
                    urls++;
                }
            }
        }

        managerApisprojects.clearApisprojects();
        managerUrls.clearUrls();

        var i;
        for (i = 0; i < urls; i++) {
            managerUrls.addUrl();
        }
        for (i = 0; i < apiskey; i++) {
            managerApisprojects.addApiproject();
        }

        modal.find('input.form-control').each(function(){
            var input = $(this);
            input.val(data[input.attr('name')]);
        });
        modal.find('textarea').each(function(){
            var input = $(this);
            input.val(data[input.attr('name')]);
        });
        modal.find('select').each(function(){
            var input = $(this);
            input.val(data[input.attr('name')]).chosen().trigger("chosen:updated");
        });

        validatorGeneralTab.form();
    });
}

function saveNewProject(){
    if(!formsValid()){
        return;
    }

    var path = btnActionModal.data('action');
    var pathSuccess = btnActionModal.data('action-success');

    var data = {
        'frontendbundle_datproject[_token]':$('#frontendbundle_datproject__token').val(),
        'frontendbundle_datproject[name]' : $('#frontendbundle_datproject_name').val(),
        'frontendbundle_datproject[applicationtype]':$('#frontendbundle_datproject_applicationtype').val(),
        'frontendbundle_datproject[description]':$('#frontendbundle_datproject_description').val()
    };

    if(btnActionModal.hasClass('edit-project')){
        data['id'] = btnActionModal.data('idproject');
    }

    var urlsComponents = $('.urls input');
    urlsComponents.each(function(){
        var url = $(this);
        if(url.val() != ''){
            data[url.attr('name')] = url.val();
        }
    });

    var apisprojects_keys = $('.apisprojects input.form-control');
    apisprojects_keys.each(function(){

        var apiproject_key = $(this);
        if(apiproject_key.val() != ''){
            data[apiproject_key.attr('name')] = apiproject_key.val();

            var idApi = apiproject_key.attr('id').replace('apikey', 'api');
            var apiproject_api = $('#'+idApi);
            data[apiproject_api.attr('name')] = apiproject_api.val();
        }
    });

    HoldOn.open();
    $.post(path,
        data,
        function (success) {
            HoldOn.close();
            if (success.success) {
                $('#modal-new-project').modal('hide');
                window.location.href=pathSuccess;
            }
            else {
                hds.msg.show(3, success.msg);
            }
        }
    ).fail(function () {
            HoldOn.close();
            hds.msg.show(3, 'Ha ocurrido un error');
        });
}

function generateKey(btn) {
    var path = $('#form-apisprojects').attr('data-action');

    btn = $(btn);
    var input = btn.parent().prev();
    var idSelect = input.attr('id').replace('apikey', 'api');
    var select = $('#'+idSelect);

    var apiId = select.val();

    if(!apiId){
        hds.msg.show(2, 'Seleccione el api para generar la clave');
        return;
    }

    var data = {
        idApi : apiId
    };

    HoldOn.open();
    $.post(path,
        data,
        function (success) {
            HoldOn.close();
            if (success.success) {
                input.val(success.key);
            }
            else {
                hds.msg.show(3, 'Ha ocurrido un error');
            }
        }
    ).fail(function () {
            HoldOn.close();
            hds.msg.show(3, 'Ha ocurrido un error');
        });
}

















