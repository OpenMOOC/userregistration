// http://jquery-howto.blogspot.kr/2009/10/javascript-jquery-password-generator.html
$.extend({
  password: function (length, special) {
    var iteration = 0;
    var password = "";
    var randomNumber;
    if(special == undefined){
        var special = false;
    }
    while(iteration < length){
        randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
        if(!special){
            if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
            if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
            if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
            if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
        }
        iteration++;
        password += String.fromCharCode(randomNumber);
    }
    return password;
  }
});

var generatePassword = function generatePassword(options)
{
    var min_length = options.requirements.minLength;
    var needs_special = options.requirements.special;
    var len;
    var use_special;
    var ret;

    if (min_length === undefined) {
        len = 10;
    } else {
        // Add 0-5 characters more
        len = min_length.value + (Math.floor((Math.random() * 100)) % 6);
    }

    ret = jQuery.password(len, true);

    // Do some checks before returning this one
    for (var key in options.requirements) {
        if ($.fn.simplePassMeter.defaults.requirements[key] !== undefined
                && $.fn.simplePassMeter.defaults.requirements[key].regex !== undefined) {
            var regex = new RegExp($.fn.simplePassMeter.defaults.requirements[key].regex);
            if (regex.test(ret) === false) {
                var rangeStart = 0;
                var rangeEnd = 0;
                switch (key) {
                    case 'upper':
                    case 'letters':
                        rangeStart = 65;
                        rangeEnd = 90;
                        break;
                    case 'numbers':
                        rangeStart = 48;
                        rangeEnd = 57;
                        break;
                    case 'lower':
                        rangeStart = 97;
                        rangeEnd = 122;
                        break;
                    case 'special':
                        rangeStart = 33;
                        rangeEnd = 47;
                        break;
                    default:
                        alert("Requisito de clave desconocido. La clave no será válida");
                        return ret;
                        break;
                }

                var new_char = (Math.floor((Math.random() * 100)) % (rangeEnd - rangeStart + 1)) + rangeStart;
                ret += String.fromCharCode(new_char);
            }
        }
    }

    return ret;
}

$(document).ready(function() {
        $('#generate-password').click(function() {
            var password = generatePassword(simplePassMeterOptions);
            $('#pw1').val(password);
            $('#pw2').val(password);
            $('#sendemail').attr('checked', 'checked');
            $('#pw1').trigger('keyup');
        });
});
