(function($) {
    'use strict';

    var settings = window.arabseenAnalytics || {};
    var strings = settings.strings || {};

    // Cache DOM elements
    var $periodSelect = $('#analytics-period');
    var $refreshButton = $('#refresh-analytics');
    var $insightsContent = $('#insights-content');

    function init() {
        // Initial load
        loadAllData();

        // Event listeners
        $refreshButton.on('click', function(e) {
            e.preventDefault();
            loadAllData();
        });

        $periodSelect.on('change', function() {
            loadAllData();
        });
    }

    function loadAllData() {
        var period = $periodSelect.val();
        
        setLoadingState(true);
        
        var promises = [
            fetchData('overview', period),
            fetchData('trends', period),
            fetchData('top_queries', period),
            fetchData('failed_searches', period),
            fetchData('languages', period),
            fetchData('insights', period)
        ];

        $.when.apply($, promises).always(function() {
            setLoadingState(false);
        });
    }

    function setLoadingState(isLoading) {
        if (isLoading) {
            $refreshButton.prop('disabled', true);
            $('.metric-value').css('opacity', '0.5');
        } else {
            $refreshButton.prop('disabled', false);
            $('.metric-value').css('opacity', '1');
        }
    }

    function fetchData(dataType, period) {
        return $.ajax({
            url: settings.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'arabic_search_analytics_data',
                nonce: settings.nonce,
                data_type: dataType,
                period: period
            },
            success: function(response) {
                if (response.success) {
                    updateDashboard(dataType, response.data);
                } else {
                    console.error('Analytics error:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', error);
            }
        });
    }

    function updateDashboard(dataType, data) {
        switch (dataType) {
            case 'overview':
                updateOverview(data);
                break;
            case 'trends':
                updateTrendsChart(data);
                break;
            case 'languages':
                updateLanguagesChart(data);
                break;
            case 'top_queries':
                updateTopQueriesTable(data);
                break;
            case 'failed_searches':
                updateFailedSearchesTable(data);
                break;
            case 'insights':
                updateInsights(data);
                break;
        }
    }

    function updateOverview(data) {
        $('#total-searches').text(data.total_searches);
        $('#unique-queries').text(data.unique_queries);
        $('#avg-results').text(data.avg_results);
        $('#success-rate').text(data.success_rate + '%');
    }

    function updateTrendsChart(data) {
        var $container = $('.chart-container').first(); // Assuming first chart is trends
        var html = renderBarChart(data.labels, data.searches); // Simplified: using 'searches' data
        $container.find('.css-bar-chart').replaceWith(html);
        if ($container.find('.css-bar-chart').length === 0) {
             $container.append(html);
        }
    }

    function updateLanguagesChart(data) {
        var $container = $('.chart-container').last(); // Assuming second chart is languages
        var formattedData = [];
        if (data.labels && data.data) {
             for (var i = 0; i < data.labels.length; i++) {
                 formattedData.push({
                     label: data.labels[i],
                     value: data.data[i]
                 });
             }
        }
        var html = renderPieChart(formattedData);
        $container.find('.css-pie-chart').replaceWith(html);
        if ($container.find('.css-pie-chart').length === 0) {
             $container.append(html);
        }
    }

    function renderBarChart(labels, values) {
        if (!values || values.length === 0) return '<p>' + strings.no_data + '</p>';
        
        var maxValue = Math.max.apply(null, values);
        var html = '<div class="css-bar-chart">';
        
        for (var i = 0; i < values.length; i++) {
            var percentage = maxValue > 0 ? (values[i] / maxValue) * 100 : 0;
            html += '<div class="bar-item">';
            html += '<div class="bar-label">' + escapeHtml(labels[i]) + '</div>';
            html += '<div class="bar-container">';
            html += '<div class="bar-fill" style="width: ' + percentage + '%"></div>';
            html += '<div class="bar-value">' + values[i] + '</div>';
            html += '</div></div>';
        }
        html += '</div>';
        return html;
    }

    function renderPieChart(data) {
        if (!data || data.length === 0) return '<p>' + strings.no_data + '</p>';
        
        var total = 0;
        data.forEach(function(item) { total += item.value; });
        
        var html = '<div class="css-pie-chart"><div class="pie-legend">';
        var colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12', '#9b59b6', '#1abc9c'];
        
        data.forEach(function(item, index) {
            var percentage = total > 0 ? ((item.value / total) * 100).toFixed(1) : 0;
            var color = colors[index % colors.length];
            
            html += '<div class="legend-item">';
            html += '<div class="legend-color" style="background-color: ' + color + '"></div>';
            html += '<div class="legend-text">' + escapeHtml(item.label) + ': ' + item.value + ' (' + percentage + '%)</div>';
            html += '</div>';
        });
        
        html += '</div></div>';
        return html;
    }

    function updateTopQueriesTable(data) {
        var $tbody = $('#top-queries-table tbody');
        $tbody.empty();
        
        if (!data || data.length === 0) {
            $tbody.append('<tr><td colspan="5">' + strings.no_data + '</td></tr>');
            return;
        }
        
        data.forEach(function(row) {
            var html = '<tr>';
            html += '<td>' + escapeHtml(row.query) + '</td>';
            html += '<td>' + row.searches + '</td>';
            html += '<td>' + row.avg_results + '</td>';
            html += '<td>' + row.success_rate + '</td>';
            html += '<td>' + row.last_searched + '</td>';
            html += '</tr>';
            $tbody.append(html);
        });
    }

    function updateFailedSearchesTable(data) {
        var $tbody = $('#failed-searches-table tbody');
        $tbody.empty();
        
        if (!data || data.length === 0) {
            $tbody.append('<tr><td colspan="4">' + strings.no_data + '</td></tr>');
            return;
        }
        
        data.forEach(function(row) {
            var html = '<tr>';
            html += '<td>' + escapeHtml(row.query) + '</td>';
            html += '<td>' + row.attempts + '</td>';
            html += '<td>' + escapeHtml(row.suggestions) + '</td>';
            html += '<td>' + row.last_attempt + '</td>';
            html += '</tr>';
            $tbody.append(html);
        });
    }

    function updateInsights(data) {
        $insightsContent.empty();
        
        if (!data || data.length === 0) {
            $insightsContent.append('<p>' + strings.no_data + '</p>');
            return;
        }
        
        data.forEach(function(insight) {
            var html = '<div class="notice notice-' + insight.type + ' inline"><p>';
            html += '<strong>' + escapeHtml(insight.title) + ':</strong> ';
            html += escapeHtml(insight.message);
            html += '</p></div>';
            $insightsContent.append(html);
        });
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Initialize on document ready
    $(document).ready(init);

})(jQuery);
