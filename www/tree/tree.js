/**
 * Created by onysko on 05.11.2014.
 */

//var searchField = s('input#search');
var searchInitiated = false;
// Ajax request handle
var searchRequest;
var searchTimeout;

function  AppProductInit(response){
    if (response !== undefined) {
        if (response.table_html !== undefined) {
            s('.field-material-table').html(response.table_html);
        }

        if (response.pager_html !== undefined) {
            s('.table-pager').html(response.pager_html);
        }
    }
    s('.products-table').fixedHeader();
    AppProductInitPagerButtons(s('.table-pager'));
}

s('.products_tree').pageInit(function(obj) {
    AppProductInit();

    s('ul', obj).addClass('tree-root');

    AppProductInitTree(obj);

    AppProductSearch(s('.field-material-table input#search'));

});

function AppProductInitPagerButtons(pager)
{
    s('a', pager).each(function (link) {
        link.ajaxClick(function (response) {
            loader.hide();
            AppProductInit(response);
        }, function() {
            // Create generic loader
            loader.show('Подождите', true);
            return true;
        });
    });
}

/**
 * Asynchronous material search
 * @param searchField Search query
 */
function AppProductSearch(searchField) {
    // Safely get object
    var search = searchField;

    var structure = 0;
    var company = 0;
    if (s('#structureId').length) {
        structure = s('#structureId').val();
    }
    var page = 1;



    // Key up handler
    search.keyup(function(obj, p, e) {
        // If we have not send any search request and this is not Enter character
        if (searchRequest == undefined && e.which != 13) {
            // Reset timeout on key press
            if (searchTimeout != undefined) clearTimeout(searchTimeout);

            // Set delayed function
            searchTimeout = window.setTimeout(function() {
                // Get search input
                var keywords = obj.val();

                if (keywords.length < 2) keywords = '';

                // Disable input
                search.DOMElement.enabled = false;

                // Avoid multiple search requests
                if (!searchInitiated) {
                    // Set flag
                    searchInitiated = true;

                    // Show loader with i18n text and black bg
                    loader.show(s('.loader-text').val(), true);

                    // Perform async request to server for rendering table
                    s.ajax(s('input#search').a('controller') + structure + '/' + company + '/' + keywords + '/' + page, function(response) {

                        response = JSON.parse(response);
                        //s('.products_tree').html(response.table_html);
                        AppProductInit(response);

                        loader.hide();

                        // Release flag
                        searchInitiated = false;
                    });
                }

            }, 1000);
        }
    });
}

function AppProductInitTree(tree)
{
    tree.treeview(
        true,
        function(tree) {
            AppProductInitTree(tree);
        }
    );

    if (!tree.hasClass('sjs-treeview')) {
        tree = s('.sjs-treeview', tree);
    }

    s('.open', tree).each(function(link) {
        link.href = link.a('href');
        link.a('href', link.href);
        link.ajaxClick(function(response) {
            s('.icon-structure').html(link.html());
            s('.open').removeClass('current');
            link.addClass('current');
            loader.hide();
            AppProductInit(response);
        }, function() {
            // Create generic loader
            loader.show('Подождите', true);
            return true;
        });
    });

    s('.product_control.material_move', tree).click(function(link) {
        var selectForm = s(".table_form");
        var selectAction = 'product/move/' + link.a('structure');

        selectForm.ajaxForm({
            'url': selectAction,
            'handler': function(respTxt){
                respTxt = JSON.parse(respTxt);
                AppProductInit(respTxt);
            }
        });

        return false;
    });

}