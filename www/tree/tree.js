/**
 * Created by Maxim Omelchenko on 25.12.2014 at 17:51.
 */

var SamsonCMS_InputMaterial = function(block) {
    var selectBlock = s('.__add_block', block);
    var deleteBlock = s('.__delete_block', block);
    var materialLink = s('.__material_link', block);
    var getAttributes = s('.__select_btn', selectBlock).a('getattr');
    var searchInitiated = false;
    var searchRequest;
    var searchTimeout;
    var box;
    var tbForm;
    var fieldLoader = new Loader(block.parent(), {type: 'absolute', top: 1, left: 1});

    // If material was set hide select block, otherwise hide delete block
    if (materialLink.html().trim().length) {
        selectBlock.hide();
    } else {
        deleteBlock.hide();
    }
    // Show CMSInputMaterial block
    block.show();

    s('.__select_btn', selectBlock).tinyboxAjax({
        html : 'html',
        oneClickClose : true,
        renderedHandler : function(form, tb) {
            s('.input_material_search_form', form).submit(function(){
                return false;
            });

            fieldMaterialTree();
            box = tb;
            tbForm = form;
        }
    });

    //
    s('.__delete_btn', block).ajaxClick(function () {
        fieldLoader.hide();
        selectBlock.show();
    }, function(){
        materialLink.html('');
        deleteBlock.hide();
        fieldLoader.show();
        return true;
    });


    function fieldMaterialInit(response) {
        if (response !== undefined) {
            if (response.table_html !== undefined) {
                s('.field-material-table').html(response.table_html);
            }

            if (response.pager_html !== undefined) {
                s('.table-pager').html(response.pager_html);
            }
        }

        fieldMaterialInitPager(s('.table-pager'));
        fieldMaterialTable();
    }

    function fieldMaterialTree() {
        var obj = s('.field_material_tree', tbForm);

        // Bind all structures link
        s('.field_material_all', tbForm).ajaxClick(function(response) {
            fieldMaterialInit(response);
            s('.structure-element .current').removeClass('current');
        }, function() {
            //loader on table
            return true;
        });

        fieldMaterialInit();
        s('ul', obj).addClass('tree-root');
        fieldMaterialInitTree(obj);
        fieldMaterialSearch(s('.field_material input#search'));
    }

    function fieldMaterialInitPager(pager) {
        var loader;
        s('a', pager).each(function (link) {
            link.ajaxClick(function (response) {
                //fmLoader.hide();
                fieldMaterialInit(response);
                loader.hide();
            }, function () {
                // Create generic loader
                //fmLoader.show('Подождите', true);
                loader = new Loader(s('.field-material-table'));
                loader.show();
                return true;
            });
        });
    }

    /**
     * Asynchronous material search
     * @param searchField Search query
     */
    function fieldMaterialSearch(searchField) {
        // Safely get object
        var search = searchField;

        var page = 1;


        // Key up handler
        search.keyup(function (obj, p, e) {
            // If we have not send any search request and this is not Enter character
            if (searchRequest == undefined && e.which != 13) {
                // Reset timeout on key press
                if (searchTimeout != undefined) clearTimeout(searchTimeout);

                // Set delayed function
                searchTimeout = window.setTimeout(function () {
                    // Get search input
                    var keywords = obj.val();

                    if (keywords.length < 2) keywords = '';

                    // Disable input
                    search.DOMElement.enabled = false;

                    // Avoid multiple search requests
                    if (!searchInitiated) {
                        // Set flag
                        searchInitiated = true;

                        var structureId = 0;
                        var temp = s('.structure-element .current').parent();
                        if (temp) {
                            structureId = s('.structure_id', temp).html();
                        }

                        // Show loader with i18n text and black bg

                        var loader = new Loader(s('.field-material-table'));
                        loader.show();
                        // Perform async request to server for rendering table
                        s.ajax(s('input#search').a('controller') + '/' + keywords + '/' + page, function (response) {
                            loader.hide();
                            response = JSON.parse(response);
                            fieldMaterialInit(response);

                            // Release flag
                            searchInitiated = false;
                        });
                    }

                }, 1000);
            }
        });
    }

    function fieldMaterialInitTree(tree) {
        tree.treeview(
            true,
            function (tree) {
                fieldMaterialInitTree(tree);
            }
        );

        if (!tree.hasClass('sjs-treeview')) {
            tree = s('.sjs-treeview', tree);
        }

        var loader;

        s('.open', tree).each(function (link) {
            link.ajaxClick(function (response) {
                s('.icon-structure').html(link.html());
                s('.open').removeClass('current');
                link.addClass('current');
                //fmLoader.hide();
                fieldMaterialInit(response);
                loader.hide();
            }, function () {
                loader = new Loader(s('.field-material-table'));
                loader.show();
                // Create generic loader
                //fmLoader.show('Подождите', true);
                return true;
            });
        });
    }

    function fieldMaterialTable() {
        s('.field_material_confirm', tbForm).each(function (item) {
            var href = item.a('href');
            href += getAttributes;
            item.a('href', href);
            item.ajaxClick(function (response) {
                box.close();
                fieldLoader.hide();
                deleteBlock.show();
                materialLink.html(response.material);
                materialLink.a('href', response.url);
            }, function(){
                selectBlock.hide();
                fieldLoader.show();
                return true;
            });
        });
    }
};

// Bind input
SamsonCMS_Input.bind(SamsonCMS_InputMaterial, '.__fieldmaterial');