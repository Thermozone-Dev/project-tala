<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="{{base_path('public/css/bootstrap_trimmed.min.css')}}" />
        <style>
            .footer {
                font-size: 16px;
            }
            .paging {
                text-align: right;
            }
        </style>

        <script>
            function substitutePdfVariables() {

                function getParameterByName(name) {
                    var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
                    return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
                }

                function substitute(name) {
                    var value = getParameterByName(name);
                    var elements = document.getElementsByClassName(name);

                    for (var i = 0; elements && i < elements.length; i++) {
                        elements[i].textContent = value;
                    }
                }

                ['frompage', 'topage', 'page', 'webpage', 'section', 'subsection', 'subsubsection']
                    .forEach(function(param) {
                        substitute(param);
                    });
            }
        </script>
    </head>
    <body onload="substitutePdfVariables()">
        <div class="row">
            <div class="col-xs-12">
                <p class="footer paging">Page <span class="page"></span></p>
            </div>
        </div>
    </body>
</html>
