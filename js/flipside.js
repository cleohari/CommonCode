/*jslint browser: true, sloppy: true, vars: true, white: true */
/*global docElement: false, $: false*/

function getParameterByName(name)
{
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return (results === null) ? null : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function browser_supports_cors()
{
    var xhr = new XMLHttpRequest();
    if(xhr !== null)
    {
        if(xhr.hasOwnProperty('withCredentials') || xhr.withCredentials !== undefined)
        {
            return true;
        }
    }
    if(typeof XDomainRequest !== 'undefined')
    {
        return true;
    }
    return false;
}

function browser_supports_font_face_css2(rule)
{
    var result = false;
    var head = document.head || document.getElementsByTagName('head')[0] || docElement;
    var style = document.createElement("style");

    style.type = 'text/css';
    head.insertBefore(style, head.firstChild);
    var sheet = style.sheet || style.styleSheet;
    try
    {
        sheet.insertRule(rule, 0);
        result = !(/unknown/i).test(sheet.cssRules[0].cssText);
        sheet.deleteRule(sheet.cssRules.length - 1);
    } catch(ignore)
    {
        console.log(ignore);
    }
    return result;
}

function browser_supports_font_face_older(rule)
{
    var head = document.head || document.getElementsByTagName('head')[0] || docElement;
    var style = document.createElement("style");

    style.type = 'text/css';
    head.insertBefore(style, head.firstChild);
    var sheet = style.sheet || style.styleSheet;
    sheet.cssText = rule;

    return sheet.cssText.length !== 0 && !(/unknown/i).test(sheet.cssText) &&
           sheet.cssText.replace(/\r+|\n+/g, '').indexOf(rule.split(' ')[0]) === 0;
}

function browser_supports_font_face()
{
    var impl = document.implementation || { hasFeature: function() {return false;}};

    var rule = '@font-face { font-family: "font"; src: "font.ttf"; }';
    if(impl.hasFeature('CSS2', ''))
    {
        return browser_supports_font_face_css2(rule);
    }
    return browser_supports_font_face_older(rule);
}

function browser_supports_input_type(type)
{
    var i = document.createElement("input");
    i.setAttribute("type", type);
    return i.type !== "text";
}

var NOTIFICATION_SUCCESS = "alert-success";
var NOTIFICATION_INFO    = "alert-info";
var NOTIFICATION_WARNING = "alert-warning";
var NOTIFICATION_FAILED  = "alert-danger";

function add_notification(container, message, severity, dismissible)
{
    if(severity === undefined)
    {
        severity = NOTIFICATION_INFO; 
    }
    if(dismissible === undefined)
    {
        dismissible = true;
    }
    var class_str = 'alert '+severity;
    if(dismissible)
    {
        class_str+=' alert-dismissible';
    }
    var alert_div = $('<div/>', {'class': class_str, role: 'alert'});
    if(dismissible)
    {
        var button = $('<button/>', {type: 'button', 'class': 'close', 'data-dismiss': 'alert'});
        $('<span/>', {'aria-hidden': 'true'}).html('&times;').appendTo(button);
        $('<span/>', {'class': 'sr-only'}).html('Close').appendTo(button);
        button.appendTo(alert_div);
    }
    var prefix = '';
    switch(severity)
    {
        case NOTIFICATION_INFO:
            prefix = '<strong>Notice:</strong> ';
            break;
        case NOTIFICATION_WARNING:
            prefix = '<strong>Warning!</strong> ';
            break;
        case NOTIFICATION_FAILED:
            prefix = '<strong>Warning!</strong> ';
            break;
    }
    alert_div.append(prefix+message);
    container.prepend(alert_div);
}

function addButtonToFooter(footer, buttonData)
{
    var btn_class = 'btn-default';
    var options = {'type': 'button'};
    if(buttonData.style !== undefined)
    {
        btn_class = buttonData.style;
    }
    options['class'] = 'btn '+btn_class;
    if(buttonData.close !== undefined && buttonData.close === true)
    {
        options['data-dismiss'] = 'modal';
    }
    var button = $('<button/>', options).html(buttonData.text);
    if(buttonData.method !== undefined)
    {
        button.on('click', buttonData.method);
    }
    if(buttonData.data !== undefined)
    {
        button.data('data', buttonData.data);
    }
    button.appendTo(footer);
}

function create_modal(title, body, buttons)
{
    var modal = $('<div/>', {'class': 'modal fade'});
    var dialog = $('<div/>', {'class': 'modal-dialog'});
    var content = $('<div/>', {'class': 'modal-content'});
    var header = $('<div/>', {'class': 'modal-header'});
    var button = $('<button/>', {'type': 'button', 'class': 'close', 'data-dismiss': 'modal'});
    var span = $('<span/>', {'aria-hidden': 'true'}).html('&times;');
    span.appendTo(button);
    span = $('<span/>', {'class': 'sr-only'}).html('Close');
    span.appendTo(button);
    button.appendTo(header);
    span = $('<h4/>', {'class': 'modal-title'}).html(title);
    span.appendTo(header);
    header.appendTo(content);
    var div = $('<div/>', {'class': 'modal-body'}).html(body);
    div.appendTo(content);
    var footer = $('<div/>', {'class': 'modal-footer'});
    for(var i = 0; i < buttons.length; i++)
    {
        addButtonToFooter(footer, buttons[i]);
    }
    footer.appendTo(content);
    content.appendTo(dialog);
    dialog.appendTo(modal);
    return modal;
}

function string_startswith(str)
{
    return this.slice(0, str.length) === str;
}

if(typeof String.prototype.startsWith !== 'function')
{
    // eslint-disable-next-line no-extend-native
    String.prototype.startsWith = string_startswith;
}

function browser_supported()
{
    if(!$.support.ajax)
    {
        window.location = '/badbrowser.php?no-ajax';
    }
}

function browser_supports_image_upload()
{
    if (navigator.userAgent.match(/(Android (1.0|1.1|1.5|1.6|2.0|2.1))|(Windows Phone (OS 7|8.0))|(XBLWP)|(ZuneWP)|(w(eb)?OSBrowser)|(webOS)|(Kindle\/(1.0|2.0|2.5|3.0))/))
    {
        return false;
    }
    var elem = document.createElement('input');
    elem.type = 'file';
    return !elem.disabled;
}

function flipside_init()
{
    browser_supported();
    var host = window.location.hostname.split('.')[0];
    var link = $('#site_nav a[href^="https://'+host+'"]');
    link.parent().addClass('active');
}

$(flipside_init);

$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
