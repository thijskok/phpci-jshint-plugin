var jshintPlugin = ActiveBuild.UiPlugin.extend({
    id: 'build-jshint-warnings',
    css: 'col-lg-6 col-md-12 col-sm-12 col-xs-12',
    title: 'JSHint',
    lastData: null,
    displayOnUpdate: false,
    box: true,
    rendered: false,

    register: function() {
        var self = this;
        var query = ActiveBuild.registerQuery('jshint-data', -1, {key: 'jshint-data'})

        $(window).on('jshint-data', function(data) {
            self.onUpdate(data);
        });

        $(window).on('build-updated', function() {
            if (!self.rendered) {
                self.displayOnUpdate = true;
                query();
            }
        });
    },

    render: function() {

        return $('<div class="table-responsive"><table class="table" id="jshint-data">' +
            '<thead>' +
            '<tr>' +
            '   <th>'+Lang.get('file')+'</th>' +
            '   <th>'+Lang.get('line')+'</th>' +
            '   <th>Severity</th>' +
            '   <th>'+Lang.get('message')+'</th>' +
            '</tr>' +
            '</thead><tbody></tbody></table></div>');
    },

    onUpdate: function(e) {
        if (!e.queryData) {
            $('#build-jshint-warnings').hide();
            return;
        }

        this.rendered = true;
        this.lastData = e.queryData;

        var errors = this.lastData[0].meta_value;
        var tbody = $('#jshint-data tbody');
        tbody.empty();

        if (errors.length == 0) {
            $('#build-jshint-warnings').hide();
            return;
        }

        for (var i in errors) {
            var file = errors[i].file;

            if (ActiveBuild.fileLinkTemplate) {
                var fileLink = ActiveBuild.fileLinkTemplate.replace('{FILE}', file);
                fileLink = fileLink.replace('{LINE}', errors[i].line);

                file = '<a target="_blank" href="'+fileLink+'">' + file + '</a>';
            }

            var row = $('<tr>' +
                '<td>'+file+'</td>' +
                '<td>'+errors[i].line+'</td>' +
                '<td>'+errors[i].severity+'</td>' +
                '<td>'+errors[i].message+'</td></tr>');

            tbody.append(row);
        }

        $('#build-jshint-warnings').show();
    }
});

ActiveBuild.registerPlugin(new jshintPlugin());
