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
    var fieldLoader = new Loader(block.parent(), {type: 'absolute', top: 1, left: 1});

    // If material was set hide select block, otherwise hide delete block
    if (materialLink.html().trim().length) {
        selectBlock.hide();
    } else {
        deleteBlock.hide();
    }
    // Show CMSInputMaterial block
    block.show();

    // Get and show material selection box on select button click
    s('.__select_btn', selectBlock).ajaxClick(function (response) {
        box = s(response.html);
        box.hide();
        box.appendTo('body');
        box = tinybox(box, true, true, true);
        box.show();
        // Bind tree in this box
        fieldMaterialTree();
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
        s('.field-material-table').fixedHeader();
        fieldMaterialInitPager(s('.table-pager'));
        fieldMaterialTable();
    }

    function fieldMaterialTree() {

        var obj = s('.field_material_tree', box);

        // Bind all structures link
        s('.field_material_all', box).ajaxClick(function(response) {
            fieldMaterialInit(response);
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
        s('a', pager).each(function (link) {
            link.ajaxClick(function (response) {
                //fmLoader.hide();
                fieldMaterialInit(response);
            }, function () {
                // Create generic loader
                //fmLoader.show('Подождите', true);
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
                        //fmLoader.show(s('.loader-text').val(), true);

                        // Perform async request to server for rendering table
                        s.ajax(s('input#search').a('controller') + '/' + structureId + '/' + keywords + '/' + page, function (response) {

                            response = JSON.parse(response);
                            //s('.products_tree').html(response.table_html);
                            fieldMaterialInit(response);

                            //fmLoader.hide();

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

        s('.open', tree).each(function (link) {
            link.ajaxClick(function (response) {
                s('.icon-structure').html(link.html());
                s('.open').removeClass('current');
                link.addClass('current');
                //fmLoader.hide();
                fieldMaterialInit(response);
            }, function () {
                // Create generic loader
                //fmLoader.show('Подождите', true);
                return true;
            });
        });
    }

    function fieldMaterialTable() {
        s('.field_material_confirm', box).each(function (item) {
            var href = item.a('href');
            href += getAttributes;
            item.a('href', href);
            item.ajaxClick(function (response) {
                box.close();
                fieldLoader.hide();
                deleteBlock.show();
                materialLink.html(response.material);
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