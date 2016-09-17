<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Relax...</title>
    <meta name="HandheldFriendly" content="True">
    <meta name="viewport" content="initial-scale = 1.0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">

    <link rel="stylesheet" href="vendor/css/grid.min.css" type="text/css">
    <link rel="stylesheet" href="vendor/css/meteo/stylesheet.min.css" type="text/css">
    <link rel="stylesheet" href="vendor/css/fontello/fontello.min.css" type="text/css">
    <link rel="stylesheet" href="vendor/css/glyphicons.min.css" type="text/css">
    <link rel="stylesheet" href="vendor/css/editor.min.css"/>
    <link rel="stylesheet" href="static/css/fitgrd.pack.css" type="text/css">
    <link rel="stylesheet" href="static/css/style.min.css" type="text/css">
    <link rel="stylesheet" href="static/css/preview.min.css" type="text/css">
</head>
<body>
<div class="wrapper">

    <header>
        <div class="center">
            <div class="row">
                <div class="fg12 title-container">
                    <h1>Relax...</h1>
                </div>
            </div>
        </div>
    </header>

    <section>
        <div class="center">
            <div class="row">

                <div class="fg10">
                    <div class="editor-wrapper">
                        <form action="" id="editor">
                            <textarea placeholder="Please relax and type away..."></textarea>
                        </form>
                    </div>
                </div>

                <div class="fg2 sidebar">
                    <div id="soundlinks"></div>
                    <hr>
                    <div class="toolbar">
                        <p id="btn-save-wrapper">
                            <a id="btn-save" href="#"><i class="glyphicon glyphicon-save"></i> Save as...</a>
                        </p>
                        <hr>
                        <p>
                            <i class="glyphicon glyphicon-open"></i> Open
                        </p>
                        <p id="document-list"></p>
                    </div>
                    <hr>
                    <small class="notes">
                        made with love by <a href="http://jehaisleprintemps.net">Bruno Bord</a>
                        - &copy; 2014. Grab <a href="https://github.com/brunobord/relax">the source on Github</a>,
                        inspired by the fantastic concept of <a href="http://noisli.com">Noisli.com</a>
                    </small>
                </div>

            </div>
        </div>
    </section>

</div> <!-- /wrapper -->


<script type="text/javascript" src="vendor/js/jquery.min.js"></script>
<script type="text/javascript" src="vendor/js/jquery.jplayer.min.js"></script>
<script type="text/javascript" src="vendor/js/marked.min.js"></script>
<script type="text/javascript" src="vendor/js/codemirror.min.js"></script>
<script type="text/javascript" src="vendor/js/intro.min.js"></script>
<script type="text/javascript" src="vendor/js/editor.min.js"></script>
<script src="static/js/load.min.js"></script>
<script type="text/javascript">

    if (!String.prototype.startsWith) {
        Object.defineProperty(String.prototype, 'startsWith', {
            enumerable: false,
            configurable: false,
            writable: false,
            value: function (searchString, position) {
                position = position || 0;
                return this.indexOf(searchString, position) === position;
            }
        });
    }

    var myEditor;
    $(document).ready(function () {

        var prefix = 'relax';

        // Load localStorage
        var content = '';
        if (localStorage[prefix]) {
            content = localStorage[prefix];
        }

        myEditor = new Editor();
        myEditor.render();
        myEditor.codemirror.setValue(content);

        var intervalID = window.setInterval(function () {
            myEditor.element.value = myEditor.codemirror.getValue();
            localStorage[prefix] = myEditor.element.value;
        }, 500);


        function loadDocuments() {
            // clear the list
            var documentList = $('#document-list');
            documentList.html('');
            // load from localStorage
            for (var k in localStorage) {
                if (!localStorage.hasOwnProperty(k)) {
                    continue;
                }
                if (k.startsWith(prefix + ':')) {
                    var link;
                    link = $('<a/>').attr({
                        'href': '#',
                        'class': 'load',
                        'data-document': k
                    });
                    link.html(" " + k.replace(prefix + ':', ''));
                    documentList.append(link);

                    link = $('<a/>').attr({
                        'href': '#',
                        'class': 'delete',
                        'data-document': k
                    });
                    link.html('<i class="glyphicon glyphicon-trash"></i>');
                    documentList.append(' ').append(link);

                    link = $('<a/>').attr({
                        'href': '#',
                        'class': 'download',
                        'data-document': k
                    });
                    link.html('<i class="glyphicon glyphicon-download"></i>');
                    documentList.append(' ').append(link);

                    documentList.append('<br/>');
                }
            }

            $('.load').on('click', function () {
                var name = $(this).data('document');
                myEditor.codemirror.setValue(localStorage[name]);
            });

            $('.delete').on('click', function () {
                var name = $(this).data('document');
                localStorage.removeItem(name);
                loadDocuments();
            });

            $('.download').on('click', function () {
                var name = $(this).data('document');
                var data = localStorage[name];
                var filename = name.replace(prefix + ':', '') + '.md';
                saveTextAsFile(data, filename);
            })

        }

        loadDocuments();

        $('#btn-save').on('click', function () {
            ok = false;
            while (!ok) {
                var name = prompt("File name?");
                if (name === null) break;
                if (localStorage[prefix + ':' + name] === undefined) {
                    ok = true;
                } else {
                    if (confirm('Are you sure you want to overwrite `' + name + '`?')) {
                        ok = true;
                    }
                }
            }
            localStorage[prefix + ':' + name] = localStorage[prefix];
            loadDocuments();
        });

        function destroyClickedElement(event) {
            document.body.removeChild(event.target);
        }

        function saveTextAsFile(text, filename) {
            var textFileAsBlob = new Blob([text], {type: 'text/plain'});

            var downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.innerHTML = "Download File";
            if (window.webkitURL != null) {
                // Chrome allows the link to be clicked
                // without actually adding it to the DOM.
                downloadLink.href = window.webkitURL.createObjectURL(textFileAsBlob);
            } else {
                // Firefox requires the link to be added to the DOM
                // before it can be clicked.
                downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
                downloadLink.onclick = destroyClickedElement;
                downloadLink.style.display = "none";
                document.body.appendChild(downloadLink);
            }

            downloadLink.click();
        }

    });
</script>
</body>
</html>
