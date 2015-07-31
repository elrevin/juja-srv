function getSliderFilterValue(el) {
    if (el.val() != el.attr('data-slider-min')+','+el.attr('data-slider-max')) {
        return el.val().replace(",", ":");
    }
    return null;
}

function getListFilterValues(elems) {
    val = [];

    elems.each(function (i, el) {
        el = $(el);
        val[val.length] = el.attr('url');
    });

    if (val.length) {
        return val.join(':');
    }

    return null;
}

$(document).ready(function () {
    $(".rangeSlider").slider({});
    $("#apply-goods-params").click(function () {
        var url = $(this).attr('url'),
            filter = [], elems, el, val, params, i;

        el = $("[catalogFilter='property'][name='price']");
        val = getSliderFilterValue(el);
        if (val) {
            filter[filter.length] = 'property_'+el.attr('name')+'='+val;
        }

        elems = $("[catalogFilter='property'][name='brand']:checked");
        val = getListFilterValues(elems);

        elems.each(function (i, el) {
            el = $(el);
            val[val.length] = el.attr('url');
        });

        if (val) {
            filter[filter.length] = 'property_brand='+val;
        }

        elems = $("[catalogFilter='param']");
        params = [];
        elems.each(function (index, el) {
            el = $(el);
            if (!params[el.attr('name')]) {
                params[el.attr('name')] = el.attr('paramType');
            }
        });

        for (i in params) {
            if (params[i] == 'int' || params[i] == 'float') {
                el = $("[catalogFilter='param'][name='"+i+"']");
                val = getSliderFilterValue(el);
                if (val) {
                    filter[filter.length] = i+'='+val;
                }
            } else if (params[i] == 'string' || params[i] == 'select') {
                el = $("[catalogFilter='param'][name='"+i+"']:checked");
                val = getListFilterValues(el);
                if (val) {
                    filter[filter.length] = i+'='+val;
                }
            }
        }

        if (filter.length) {
            window.location = url+'_filter_/'+filter.join('/');
        } else {
            window.location = url;
        }

        return false;
    });
});