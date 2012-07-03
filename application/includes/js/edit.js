window.mw_editables_created = false;
window.mw_element_id = false;

window.mw_text_edit_started = false;
window.mw_sortables_created = false;
window.mw_drag_started = false;
window.mw_sorthandle_hover = false;
window.mw_row_id = false;
window.mw_empty_column_placeholder = '<div class="ui-state-highlight ui-sortable-placeholder"><span>Please drag items here 1</span></div>';
window.mw_empty_column_placehoerferlder = '';
window.mw_empty_column_placeholder11 = '<div class="ui-state-highlight ui-sortable-placeholder"><span>Please drop items here 2</span></div>';
window.mw_empty_column_placeholder2 = '<div class="element empty-element"><span>Please drag items here 3</span></div>';
window.mw_empty_column_placeholder2 = '';
window.mw_empty_column_placeholder3 = '<div class="empty-column empty-column-big"><span>Please drag items here 3</span></div>';



window.mw_sorthandle_row = "<div class='mw-sorthandle mw-sorthandle-row'><div class='columns_set'></div><div class='mw_row_delete mw_delete_element'>&nbsp;</div></div>";

window.mw_sorthandle_row_columns_controlls = 'row <a  href="javascript:mw_make_cols(ROW_ID,1)" class="mw-make-cols mw-make-cols-1" >1</a> <a  href="javascript:mw_make_cols(ROW_ID,2)" class="mw-make-cols mw-make-cols-2" >2</a> <a  href="javascript:mw_make_cols(ROW_ID,3)" class="mw-make-cols mw-make-cols-3" >3</a> <a  href="javascript:mw_make_cols(ROW_ID,4)" class="mw-make-cols mw-make-cols-4" >4</a> <a  href="javascript:mw_make_cols(ROW_ID,5)" class="mw-make-cols mw-make-cols-5" >5</a> ';

window.mw_sorthandle_row_delete = '<a class=\"mw_delete_element\" href="javascript:mw_delete_element(ROW_ID)">x</a> ';
window.mw_sorthandle_delete_confirmation_text = "Are you sure you want to delete this element?";




window.mw_sorthandle_col = "<div class='mw-sorthandle mw-sorthandle-col'><div class='columns_set'>element</div><div class='mw_col_delete mw_delete_element'><a class=\"mw_delete_element\" href=\"javascript:mw_delete_element(ELEMENT_ID)\">x</a></span></div>";

window.mw_sorthandle_module = "<div class='mw-sorthandle mw-sorthandle-col'><div class='columns_set'>MODULE_NAME</div><div class='mw_col_delete mw_delete_element'><a href=\"javascript:mw_module_settings(MODULE_ID)\">settings</a><a class=\"mw_delete_element\" href=\"javascript:mw_delete_element(ELEMENT_ID)\">x</a></span></div>";






function mw_delete_element($el_id) {
    var r = confirm(window.mw_sorthandle_delete_confirmation_text);
    if (r == true) {
        if ($el_id == undefined || $el_id == 'undefined') {
            $el_id = window.mw_element_id;
        }
        //	alert($el_id);
        $($el_id).remove();
        $('#' + $el_id).remove();
        $(".column").putPlaceholdersInEmptyColumns()
        mw_fix_grid_sizes()

    }
}

function mw_make_cols($row_id, $numcols) {



    $('.column').resizable("destroy");
    $('.ui-resizable').resizable("destroy");


    if ($row_id != undefined && $row_id != false && $row_id != 'undefined') {
        $el_id = $row_id;

    } else {
        $el_id = window.mw_row_id;

    }


    if ($el_id != undefined && $el_id != false && $el_id != 'undefined') {
        window.mw_sortables_created = false;
        // $('#'+$el_id).columnize({ columns: $numcols, target:'#'+$el_id, buildOnce:true  });
        $exisintg_num = $('#' + $el_id).children(".column").size();


        if ($numcols == 0) {
            $numcols = 1;
        }
        $exisintg_num = parseInt($exisintg_num);
        $numcols = parseInt($numcols);


        if ($exisintg_num == 0) {
            $exisintg_num = 1;
        }


        if ($numcols != $exisintg_num) {



            if (window.console && window.console.log) {
                window.console.log('  $exisintg_num ' + $exisintg_num + '       $numcols ' + $numcols);
            }

            if ($numcols > $exisintg_num) {

                for (i = $exisintg_num; i < $numcols; i++) {
                    $('<div class="column">' + window.mw_empty_column_placeholder + '</div>').appendTo('#' + $el_id);
                }

            } else {


                $cols_to_remove = $exisintg_num - $numcols;
                if (window.console && window.console.log) {
                    window.console.log('$cols_to_remove' + $cols_to_remove);
                }


                if ($cols_to_remove > 0) {

                    for (i = $cols_to_remove; i > 0; i--) {
                        //for (i=0;i<=$cols_to_remove;i++){
                        $ch_n = parseInt($exisintg_num) - parseInt(i);
                        if ($cols_to_remove >= 1) {
                            if (window.console && window.console.log) {
                                window.console.log('$removinc child col' + '#' + $el_id + ">div.column:nth-child(" + $ch_n + ")");
                            }

                            $('#' + $el_id).children(".column:eq(" + $ch_n + ")").fadeOut('slow').remove();

                        }


                    }


                }
            }






            $exisintg_num = $('#' + $el_id).children(".column").size();

            $eq_w = 100 / $exisintg_num;
            $pad = 1;
            $eq_w1 = $eq_w - $pad;
            $('#' + $el_id).children(".column").width($eq_w1 + '%');
            $('#' + $el_id).children(".column").css('float', 'left');
            $('#' + $el_id).children(".column").css('padding-right', $pad + '%');

            $('#' + $el_id).equalWidths().equalHeights();
            $('#' + $el_id).children('.column').height('auto');

            init_sortables()






        }









    }




}

function mw_fix_grid_sizes() {

    $('.row').equalWidths();
    $('.column').height('auto');
}

function mw_make_row_editor($el_id) {

    if ($el_id == undefined || $el_id == 'undefined') {
        $el_id = window.mw_row_id;
    } else {
        window.mw_row_id = $el_id;
    }
    $(".mw-layout-edit-curent-row-element").html($el_id);

    $exisintg_num = $('#' + $el_id).children(".column").size();
    text = window.mw_sorthandle_row_columns_controlls
    if (text != undefined) {
        text = text.replace(/ROW_ID/g, "'" + '' + $el_id + "'");

        $('#' + $el_id).children("div:first").find(".columns_set").html(text);
    }
    text1 = window.mw_sorthandle_row_delete
    if (text1 != undefined) {
        text1 = text1.replace(/ROW_ID/g, "'" + '' + $el_id + "'");
        $('#' + $el_id).children("div:first").find(".mw_row_delete").html(text1);

    }


    $(".mw-make-cols", '#' + $el_id).removeClass('active');
    $(".mw-make-cols-" + $exisintg_num, '#' + $el_id).addClass('active');

    // alert($exisintg_num);
}

function mw_load_new_dropped_modules() {
    $need_re_init = false;
    $(".module_draggable", '.edit').each(function (c) {

        $(this).unwrap(".module-item");


        $name = $(this).attr("data-module-name");
        if ($name && $name != 'undefined' && $name != false && $name != '') {
            $el_id_new = 'mw-col-' + new Date().getTime() + Math.floor(Math.random() * 101);
            $(this).after("<div class='element mw-module-wrap' id='" + $el_id_new + "'></div>");
            //  $(this).attr('id', $el_id_column);	
            mw.load_module($name, '#' + $el_id_new);

            $(this).fadeOut().remove();

        }




        $name = $(this).attr("data-element-name");
        if ($name && $name != 'undefined' && $name != false && $name != '') {
            $el_id_new = 'mw-layout-element-' + new Date().getTime() + Math.floor(Math.random() * 101);
            $(this).after("<div class='mw-layout-holder' id='" + $el_id_new + "'></div>");
            //  $(this).attr('id', $el_id_column);	
            mw.load_layout_element($name, '#' + $el_id_new);

            $('#' + $el_id_new).children(':first').unwrap('.mw-layout-holder');




            $(this).fadeOut().remove();
        }








        $need_re_init = true;

    })













    if ($need_re_init == true) {

        if (window.mw_drag_started == false) {
            $('.column', '.edit').resizable("destroy");
            $('.ui-resizable').resizable("destroy");

            $('.edit').sortable('destroy');
            $('.element').sortable('destroy');
            $('.column').sortable('destroy');
            $('.row').sortable('destroy');
            $('.modules-list').sortable('destroy');


            window.mw_sortables_created = false;
            setTimeout("init_sortables()", 300)


            setTimeout("mw_fix_grid_sizes()", 500)
            setTimeout("make_events()", 500)
            setTimeout("mw_make_handles()", 600)
        }

        //mw_make_handles()
    }
}


function mw_make_handles() {

    if (window.mw_drag_started == false) {

        $('.row', '.edit').each(function (index) {





            $has = $(this).children("div:first").hasClass("mw-sorthandle-row");
            if ($has == false) {
                $(this).prepend(window.mw_sorthandle_row);
            }

            $el_id = $(this).attr('id');
            if ($el_id == undefined || $el_id == 'undefined') {
                $el_id = 'mw-row-' + new Date().getTime() + Math.floor(Math.random() * 101);
                $(this).attr('id', $el_id);
            }


            mw_make_row_editor($el_id)
        })



        $('.element:not(.empty-element)').each(function (index) {

            $el_id = $(this).attr('id');
            if ($el_id == undefined || $el_id == 'undefined') {
                $el_id = 'mw-element-' + new Date().getTime() + Math.floor(Math.random() * 101);
                $(this).attr('id', $el_id);
            }

            $has = $(this).children(":first").hasClass("mw-sorthandle-col");
            if ($has == false) {
                $has_module = $(this).children(".module").size();



                if ($has_module == false) {
                    text = window.mw_sorthandle_col
                } else {
                    $m_name = $(this).children(".module").attr('data-module-title');

                    $m_id = $(this).children(".module").attr('module_id');
                    text = window.mw_sorthandle_module
                    text = text.replace(/MODULE_NAME/g, "" + '' + $m_name + "");
                    text = text.replace(/MODULE_ID/g, "'" + $m_id + "'");


                }
                text = text.replace(/ELEMENT_ID/g, "'" + '' + $el_id + "'");


                $(this).prepend(text);
            } else {


            }






        })
        mw_z_index_fix();



    }

}


function mw_z_index_fix() {

    var count = 100;
    $('.mw-sorthandle-row').each(function () {
        // If any label overlaps with the image (used overlaps plugin)

        // Increase count (the z-index)
        count += 10;
        $(this).css('z-index', count);


    });

    var count = 6000;
    $('.mw-sorthandle-col').each(function () {
        count += 10;
        $(this).css('z-index', count);
    });

    var count = 20000;
    $('.uiasd-resizable-handle').each(function () {
        count -= 10;
        $(this).css('z-index', count);
    });




}

function mw_make_css_editor($el_id) {
    if ($el_id == undefined || $el_id == 'undefined') {
        $el_id = window.mw_element_id;
    } else {
        window.mw_element_id = $el_id;
    }
    $(".mw-layout-edit-curent-element").html($el_id);
}

function mw_make_editables() {





    if (window.mw_drag_started == false && window.mw_handle_hover != true) {
        window.mw_sortables_created = false;
        if (window.mw_editables_created == false) {
            $(".edit [draggable='true']").unbind();
            $(".edit [draggable='true']").removeAttr('draggable');


            $('.mw-sorthandle').remove();
            $('.edit').sortable('destroy');
            $('.element').sortable('destroy');
            $('.column').sortable('destroy');
            $('.row').sortable('destroy');
            $(".row,.element", '.edit').enableSelection();
            $(".mw-sorthandle", '.edit').disableSelection();


            $(".edit").freshereditor("edit", true);
            window.mw_editables_created = true
            $("#mw-layout-edit-site-top-bar-r").html("Text edit");

        }

    }


}






function mw_remove_editables() {

    window.mw_text_edit_started = false;
    window.mw_editables_created = false;
    $(".edit").freshereditor("edit", false);

}


$(".column").putPlaceholdersInEmptyColumns()

function init_sortables() {
    // $('#mercury_iframe').contents().find('.edit').html('Hey, i`ve changed content of  body>! Yay!!!');


    mw_remove_editables()

    if (window.mw_sortables_created == false) {

        $('.element:not([contenteditable=false])').freshereditor("edit", false);

        var place1 = window.mw_empty_column_placeholder;
        var place2 = window.mw_empty_column_placeholder;
        $(".column", '.edit').each(function (c) {
            if ($("div", this).size() == 0) {
                //     $(this).html(place2);
            }
        })

        $('.edit').sortable('destroy');
        $('.element').sortable('destroy');
        $('.column').sortable('destroy');
        $('.row').sortable('destroy');
        $('.modules-list').sortable('destroy');

        $('.element', '.edit').sortable('destroy');
        $(".column").putPlaceholdersInEmptyColumns()

        $('.row').equalHeights()
        // $('.row>.column>.row').addClass('hl2')


        $spans = '.edit div.span1,.edit div.span1,.edit  div.span2,.edit div.span3,.edit div.span4,.edit div.span5,.edit div.span6,.edit div.span7,.edit div.span8,.edit div.span9,.edit div.span10,.edit div.span11,.edit div.span12,div.column';

        $($spans).addClass('column');

        $drop_areas = '.edit,.column,.element>.row>.column,.element>.row>.column>.element,.element>*';

        $sort_opts = {
            items: 'li.module-item,.row,.empty,.edit>.row,.element>.row,.column>.element>.row,.element>*,.element>.row,.column>.row,.column>.element>.row,.row,.row>.column>.row',
            dropOnEmpty: true,
            forcePlaceholderSize: true,
            //  forceHelperSize: true,
            greedy: true,
            tolerance: 'pointer',
            cancel: 'div.empty-element',
            cursorAt: {
                top: -2,
                left: -2
            },
            distance: 5,
            scrollSensitivity: 50,
            delay: 2,
            scroll: true,

            handle: '.mw-sorthandle-row:first',
            revert: true,

            placeholder: "ui-sortable-placeholder",
            connectWith: '.element,.edit,.row>.column,.element>.row>.column,.column,.element,.element>*,.element>.row>.column>.element>*,' + $drop_areas,
            start: function (event, ui) {

                window.mw_text_edit_started = false;
                $('.element:not([contenteditable=false])', '.edit').freshereditor("edit", false);


                $(".column").addClass('mw-outline-column');

                window.mw_drag_started = true;


                $('.row', '.edit').each(function () {
                    $rh = $(this).height();
                    $(this).children('.column').height($rh);

                });

                $(this).sortable('refreshPositions')


            },

            change: function (e, ui) {

                $(ui.placeholder).show();

                $(ui.helper).css({
                    "width": $(ui.placeholder).width()

                });
                $(ui.item).css({
                    "width": $(ui.placeholder).width()

                });
;
            },


            beforeStop: function (e, ui) {

            },

            stop: function (event, ui) {

                window.mw_drag_started = false;
                $(".column").removeClass('mw-outline-column');


                $('.eleasdasdasdament', '.edasdasdadit').each(function (index, value) {
                    var ielement = $(this).first().hasClass('row');
                    var ielement_id = $(this).first().attr('id');
                    var ielement_par_id = $(this).parent('.column').attr('id');
                    if (ielement != false && ielement_par_id != undefined) {
                        $('#' + ielement_id).moveTo('#' + ielement_par_id);
                        if (window.console && window.console.log) {
                            window.console.log(' moving rows ' + ielement_id + ' to ' + ielement_par_id);
                        }
                    }

                    var ielement = $(this).first().hasClass('element');



                    var ielement_id = $(this).first().attr('id');
                    var ielement_par_id = $(this).parent('.column').attr('id');
                    if (ielement != false && ielement_par_id != undefined) {

                        if (window.console && window.console.log) {
                            window.console.log(' moving ' + ielement_id + ' to ' + ielement_par_id);
                        }


                        $('#' + ielement_id).moveTo('#' + ielement_par_id);
                    }




                });



                $('.column').removeClass('column-outline');
                $('.ui-state-highlight').remove();
                $('.ui-sortable-placeholder').remove();

                $('.empty-element').remove();
                $('.column').height('auto');
                $('.row').height('auto');

                $(".element").css({
                    width: "auto"
                });





                mw_load_new_dropped_modules();

                $('.row').equalWidths();




                $('.row').each(function () {
                    $rh = $(this).height();
                    $(this).children('.column').height($rh);

                });






                mw_z_index_fix();


                $(this).sortable('refreshPositions')

            },


            sort: function (event, ui) {


                window.mw_drag_started = true;


            },




            over: function (event, ui) {
                $(this).children('.empty-element').show();


                window.mw_drag_started = true;







            },



            create: function (en, ui) {
                mw_make_handles()
                $(".column").putPlaceholdersInEmptyColumns()
                $(this).sortable('refreshPositions')
            },


            deactivate: function (en, ui) {
                window.mw_drag_started = false;
                $('.empty-element').hide();
                $(this).css('min-height', '10px');
            }



        }


        $('.edit').sortable($sort_opts);
        $sort_opts_elements = $sort_opts;
        $sort_opts_elements.items = '.element';
        delete $sort_opts_elements.items;

        $sort_opts_elements.handle = '.mw-sorthandle-col:first,.mw-sorthandle-row:first'




        $sort_opts2 = $sort_opts;
        delete $sort_opts2.items;


        $('.column', '.edit').sortable($sort_opts_elements);
        $('.element', '.edit').sortable($sort_opts2);
        $('.edit').sortable("refresh");







        $sort_opts_toolbar = $sort_opts;

        $sort_opts_toolbar.items = 'img';
        delete $sort_opts_toolbar.items;
        //$sort_opts_elements.items = '.element';
        $sort_opts_toolbar.handle = '.module_draggable'
        $sort_opts_toolbar.remove = function (event, ui) {
            $(ui.item).clone().appendTo(event.target);
        }



        $('.modules-list', '#mw_toolbar_tabs').sortable('destroy');
        $('.modules-list', '#mw_toolbar_tabs').sortable($sort_opts_toolbar);


        $('.modules-list', '#mw_toolbar_tabs .modules-list').disableSelection();


        $(".mw-sorthandle", '.edit').disableSelection();

        make_events()





        $("#mw-layout-edit-site-top-bar-r").html("Drag and drop edit");
        window.mw_sortables_created = true

        window.mw_sortables_created = true

    }




}


function make_events() {

    $(".element", '.edit').die('mousedown');
  //  $(">*", '.element:not([contenteditable=true])').undelegate( "mousedown" );
   // $(">*", '.element:not([contenteditable=true])').die('mousedown');
  //  $(">*", '.element:not([contenteditable=true])').unbind('mousedown');

//$('.element:not([contenteditable=true]) > *:not(.mw-sorthandle)').die('mousedown');
//
    $(".element").children().die('mousedown');
    $(">*", '.element:not([contenteditable=true])').die('mousedown');
    $(">*", '.element:not([contenteditable=true])').live('mousedown', function (e) {


//$('.element:not([contenteditable=true]) > *:not(.mw-sorthandle)').live('mousedown', function (e) {

        $is_this_module = $(this).hasClass('mw-module-wrap');
        $is_this_row = $(this).hasClass('row');
        $is_this_handle = $(this).hasClass('mw-sorthandle');
        $is_mw_delete_element = $(this).hasClass('mw_delete_element');
	$columns_set =  $(this).hasClass('columns_set');
	

        if (window.console != undefined) {
            console.log('is_this_handle: ' + $is_this_handle);
        }



        if ($is_this_handle == false && $columns_set == false && window.mw_drag_started == false && window.mw_sorthandle_hover == false && $is_this_module == false && $is_mw_delete_element == false && $is_this_row == false) {



            $(this).closest('.mw-sorthandle').show();


            $el_id = $(this).attr('id');
            if ($el_id == undefined || $el_id == 'undefined') {
                $el_id = 'mw-element-' + new Date().getTime() + Math.floor(Math.random() * 101);
                $(this).attr('id', $el_id);
            }

            $('.column').height('auto');
            window.mw_element_id = $el_id;
            mw_make_css_editor($el_id)

            window.mw_sortables_created = false;

            if (window.console != undefined) {
                console.log('contenteditable started on element id: ' + $el_id);
            }



     //       $(this).parent('.element').sortable('destroy');
          //  $(this).parent('.column').sortable('destroy');
            window.mw_text_edit_started = true;
            $(this).parent('.element:not([contenteditable=true])').freshereditor("edit", true);
            $(this).parent('.element').children('.mw-sorthandle').freshereditor("edit", false);
setTimeout("window.mw_sorthandle_hover=false", 300);
            //
          //  e.preventDefault();
	 //   e.stopPropagation();
            //event.preventDefault(); // this prevents the original href of the link from being opened
            // ..   e.stopPropagation(); // this prevents the click from triggering click events up the DOM from this element
            //   return false;

        } else {
     	window.mw_sorthandle_hover=true;
      //  setTimeout("window.mw_sorthandle_hover=false", 300);

	}

    });
    $(".module", '.edit').die('mousedown');
    //$(".mw-sorthandle").die('mousedown');



    $(".row", '.edit').die('mousedown');

    $(".row", '.edit').live('mousedown', function (e) {
        $col_panels = [];
        $el_id = $(this).attr('id');
        if ($el_id == undefined || $el_id == 'undefined') {
            $el_id = 'mw-row-' + new Date().getTime() + Math.floor(Math.random() * 101);
            $(this).attr('id', $el_id);
        }
        window.mw_row_id = $el_id;
        mw_make_row_editor($el_id)
        $exisintg_num = $('#' + $el_id).children(".column").size();
        if ($exisintg_num > 0) {
            a = 0;
            $('#' + $el_id).children(".column").each(function () {
                $col_panels[a] = [{
                    "size": $(this).width()
                }];
                $el_id_column = $(this).attr('id');
                if ($el_id_column == undefined || $el_id_column == 'undefined') {
                    $el_id_column = 'mw-column-' + new Date().getTime() + Math.floor(Math.random() * 101);
                    $(this).attr('id', $el_id_column);
                }
                a++;
            });
        }
        //e.stopPropagation();
    });


    $(".mw-sorthandle,.mw-sorthandle>*", '.edit').die('mouseover');
    $(".mw-sorthandle,.mw-sorthandle>*", '.edit').mouseover(

    function () {
        window.mw_sorthandle_hover = true;
        if (window.mw_drag_started == false) {
            $('.mw-outline').removeClass('mw-outline');
            $(this).parent().addClass('mw-outline');



        }

    }, function () {



        setTimeout("window.mw_sorthandle_hover=false", 500);




        $(this).parent().removeClass('mw-outline');
    });



    $(".mw-sorthandle", '.edit').die('mouseenter');
    $(".mw-sorthandle", '.edit').mouseenter(function () {

        $(this).show();
    })


    $(".row:not(.mw-sorthandle)", '.edit').die('mouseleave');
    $(".row:not(.mw-sorthandle)", '.edit').mouseleave(function () {

        if (window.mw_drag_started == false) {


            //$(this).find(".empty-element").hide();
            $(this).find(".mw-outline-column").removeClass('mw-outline-column');


        }
    })

    $(".row", '.edit').die('hover');


    $(".row", '.edit').hover(function (e) {

        if (window.mw_drag_started == false) {
            //$(".row").find(".mw-sorthandle").hide();
            $(".mw-sorthandle", '.edit').hide();

            //$(this).children(".mw-sorthandle-row").show();
            $has = $(this).children(":first").hasClass("mw-sorthandle-row");
            if ($has == false) {
                $(this).prepend(window.mw_sorthandle_row);
            }
            $(this).equalHeights();

            // $(".column", '.edit').removeClass("mw-outline-column");

            // $(this).children(".column").addClass("mw-outline-column");
            $(this).children(".mw-sorthandle").show();
            // $(this).find(".mw-sorthandle-col").show();
            e.stopPropagation()
        }


    },



    function () {
        if (window.mw_drag_started == false) {
            $(this).find(".mw-sorthandle").hide();
            $(this).find(".ui-resizable-handle:visible").hide();

        }
    });




    $(".element", '.edit').die('mouseover');
    $(".element", '.edit').mouseover(function () {



        if (window.mw_drag_started == false) {
            //	 $(".mw-sorthandle-col", '.edit').hide();

            //  $(".mw-sorthandle-row", '.edit').hide();
            //  $(this).parent(".column").parent(".row").children(".mw-sorthandle-row:first").show();
            //  $(this).parent(".row").children(".mw-sorthandle-row").show();


            $(this).children(".mw-sorthandle-col:hidden").show();



        }

    })

}


$('.module', '.edit').die('mouseenter');
$('.module', '.edit').live('mousenter', function (e) {
    $(this).children('[draggable]').removeAttr('draggable')
});





 $(".mw-sorthandle,.mw-sorthandle>*", '.edit').die('mousedown');
 $(".mw-sorthandle,.mw-sorthandle>*", '.edit').live('mousedown', function (e) {
    if (window.mw_sortables_created == false) {
     //   init_sortables()3
 
         $('.element[contenteditable=true]', '.edit').freshereditor("edit", false);
	 


	 $id = $(this).parent('.element').attr('id')
    $('#mw_css_editor_element_id').val($id);
    $(this).parent().attr('mw_tag_edit', $id)
    mw_show_css_editor()
    if (window.mw_sortables_created == false) {
        init_sortables()
    }



	 e.preventDefault();
            //event.preventDefault(); // this prevents the original href of the link from being opened
  	  e.stopPropagation(); // this prevents the click from triggering click events up the DOM from this element
          return false;
    }

});

$('.mw-sorthandle', '.edit').die('dblclick');
$('.mw-sorthandle', '.edit').live('dblclick', function (e) {
    $id = $(this).parent().attr('id')
    $('#mw_css_editor_element_id').val($id);
    $(this).parent().attr('mw_tag_edit', $id)
    mw_show_css_editor()
    if (window.mw_sortables_created == false) {
        init_sortables()
    }
});


$('.column', '.row').die('hover');

$('.column', '.row').live('hover', function (e) {


    if (window.mw_drag_started == false) {

        $(this).parent(".column").parent(".row").children(".mw-sorthandle-row:first").show();


        $el_id_column = $(this).attr('id');
        if ($el_id_column == undefined || $el_id_column == 'undefined') {
            $el_id_column = 'mw-column-' + new Date().getTime() + Math.floor(Math.random() * 101);
            $(this).attr('id', $el_id_column);
            $(this).addClass($el_id_column);
        }

        var parent1 = $(this).parent('.row');
        $(this).css({
            width: $(this).width() / parent1.width() * 100 + "%",
            //      height: ui.element.height()/parent.height()*100+"%"
        });


        $is_done = $(this).hasClass('ui-resizable')
        $ds = window.mw_drag_started;
        if ($is_done == false && $ds == false) {
            // $('.also-resize').removeClass('also-resize');
            //  $('.also-resize-inner').removeClass('also-resize-inner');
            $inner_column = $(this).children(".column:first");
            $prow = $(this).parent('.row').attr('id');
            //$also =  $('#'+$prow).children(".column").not("#"+$el_id_column);
            $no_next = false;
            $also = $(this).next(".column");
            $also_check_exist = $also.size();
            if ($also_check_exist == 0) {
                $no_next = true;
                $also = $(this).prev(".column");

            }

            $also_el_id_column = $also.attr('id');
            if ($also_el_id_column == undefined || $also_el_id_column == 'undefined' || $also_el_id_column == '') {
                $also_el_id_column = 'mw-column-' + new Date().getTime() + Math.floor(Math.random() * 101);
                $also.attr('id', $also_el_id_column);
            }


            $also_reverse_id = $also_el_id_column;
            // $also.attr('data-also-resize-inner', $also_reverse_id);
            //  $also.children('.column').attr('data-also-resize-inner', $also_reverse_id);

            $also_inner_items = $inner_column.attr('id');

            // $also.addClass('also-resize');
            //   $inner_column.addClass('also-resize-inner');
            $(this).parent(".column").resizable("destroy")
            $(this).children(".column").resizable("destroy")




            if ($no_next == false) {
                $handles = 'e'
            } else {
                $handles = 'none'
            }

            if ($no_next == false) {
                $(this).attr("data-also-rezise-item", $also_reverse_id)
                $(this).resizable({
                    grid: [1, 10000],
                    handles: $handles,
                    containment: "parent",
                    //	 aspectRatio: true,
                    autoHide: true,
                    cancel: ".mw-sorthandle",

                    //alsoResizeReverse:'.also-resize' ,
                    alsoResizeReverse: '#' + $also_reverse_id,
                    //	alsoResizeReverse:'.column [data-also-resize-inner='+$also_reverse_id+']' ,
                    alsoResize: '#' + $also_inner_items,

                    // alsoResize:'.also-resize-inner'  ,
                    resize: function (event, ui) {
                        $(this).css('height', 'auto');
                        ui.element.next().children(".row").equalWidths();
                        ui.element.children(".row").equalWidths();
                        ui.element.parent(".row").equalWidths();

                        $(this).parent(".row").equalHeights();

                        // $cols_to_eq =  $(this ).parent(".row").children(".column");
                        //$(this ).parent(".row").addClass('also-resize-inner');
                    },
                    create: function (event, ui) {
                        $(".row").equalWidths().equalHeights();


                    },
                    start: function (event, ui) {
                        $(".column").each(function () {
                            $(this).removeClass('selected');
                        });
                        ui.element.addClass('selected');
                    },


                    stop: function (event, ui) {
                        var parent = ui.element.parent('.row');
                        ui.element.css({
                            width: ((ui.element.width() / parent.width()) - 1) * 100 + "%",
                            //      height: ui.element.height()/parent.height()*100+"%"
                        });


                        $('.column').css('height', 'auto');
                        mw_z_index_fix();
                    }
                });





            }


        } else {
            // $(this).resizable("enable");  	
        }






        e.preventDefault();
        //event.preventDefault(); // this prevents the original href of the link from being opened
        e.stopPropagation(); // this prevents the click from triggering click events up the DOM from this element


    } else {


    }

});




$('.module', '.edit').live('click', function (e) {




    init_sortables()



    window.mw_making_sortables = false;

    $clicked_on_module = $(this).attr('module_id');
    if ($clicked_on_module == undefined || $clicked_on_module == '') {
        $clicked_on_module = $(this).attr('module_id', 'default');

    }

    if (window.console != undefined) {
        console.log('click on module 1 ' + $clicked_on_module);
    }


    if ($clicked_on_module == undefined || $clicked_on_module == '') {
        $clicked_on_module = $(this).parents('.module').attr('module_id');
    }

    if ($clicked_on_module == undefined || $clicked_on_module == '') {
        $clicked_on_module = $(this).parents('.module').attr('module_id', 'default');

    }

    $('.mw_non_sortable').removeClass('mw_non_sortable');



    // alert($clicked_on_module);


    e.preventDefault();
    //event.preventDefault(); // this prevents the original href of the link from being opened
    e.stopPropagation(); // this prevents the click from triggering click events up the DOM from this element
    return false;

});



































function closestToOffset(offset) {
    var el = null,
        elOffset, x = offset.left,
        y = offset.top,
        distance, dx, dy, minDistance;
    this.each(function () {
        elOffset = $(this).offset();

        if (
        (x >= elOffset.left) && (x <= elOffset.right) && (y >= elOffset.top) && (y <= elOffset.bottom)) {
            el = $(this);
            return false;
        }

        var offsets = [
            [elOffset.left, elOffset.top],
            [elOffset.right, elOffset.top],
            [elOffset.left, elOffset.bottom],
            [elOffset.right, elOffset.bottom]
        ];
        for (off in offsets) {
            dx = offsets[off][0] - x;
            dy = offsets[off][1] - y;
            distance = Math.sqrt((dx * dx) + (dy * dy));
            if (minDistance === undefined || distance < minDistance) {
                minDistance = distance;
                el = $(this);
            }
        }
    });
    return el;
}


(function ($) {
    $.fn.moveTo = function (selector) {
        return this.each(function () {
            var cl = $(this).clone();
            $(cl).appendTo(selector);
            $(this).remove();
        });
    }
})(jQuery);

