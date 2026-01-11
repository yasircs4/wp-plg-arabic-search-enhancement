(function($) {
    'use strict';

    var settings = window.arabseenAdmin || {};
    var strings = settings.i18n || {};

    function t(key, fallback) {
        if (Object.prototype.hasOwnProperty.call(strings, key)) {
            return strings[key];
        }

        return fallback || key;
    }

    var $button = $('#ase-run-test');

    if ($button.length) {
        var $resultsContainer = $('#ase-test-results');
        var $output = $('#ase-test-output');
        var defaultText = t('runTest', $.trim($button.text())) || $.trim($button.text());

        $button.on('click', function(event) {
            event.preventDefault();

            $button.prop('disabled', true).text(t('testRunning', 'Running Tests...'));

            var testResults = [];

            try {
                if (typeof window.jQuery !== 'undefined') {
                    testResults.push({
                        name: t('jqueryAvailable', 'jQuery Available'),
                        status: 'passed',
                        message: t('jqueryLoaded', 'jQuery is loaded')
                    });
                } else {
                    testResults.push({
                        name: t('jqueryAvailable', 'jQuery Available'),
                        status: 'warning',
                        message: t('jqueryMissing', 'jQuery not detected')
                    });
                }
            } catch (error) {
                testResults.push({
                    name: t('jqueryAvailable', 'jQuery Available'),
                    status: 'failed',
                    message: error.message || t('jqueryError', 'jQuery test failed')
                });
            }

            try {
                var testDiv = document.createElement('div');
                testDiv.innerHTML = 'قرآن كريم';
                testDiv.style.visibility = 'hidden';
                document.body.appendChild(testDiv);

                if (testDiv.offsetWidth > 0) {
                    testResults.push({
                        name: t('arabicRendering', 'Arabic Text Rendering'),
                        status: 'passed',
                        message: t('arabicRenderingPass', 'Arabic text renders correctly')
                    });
                } else {
                    testResults.push({
                        name: t('arabicRendering', 'Arabic Text Rendering'),
                        status: 'warning',
                        message: t('arabicRenderingWarn', 'Arabic text rendering may have issues')
                    });
                }

                document.body.removeChild(testDiv);
            } catch (error) {
                testResults.push({
                    name: t('arabicRendering', 'Arabic Text Rendering'),
                    status: 'failed',
                    message: error.message || t('arabicRenderingError', 'Arabic rendering test failed')
                });
            }

            var html = '';
            html += '<div class="notice notice-info"><p><strong>' + t('clientTestsComplete', 'Client-side tests completed.') + '</strong></p></div>';
            html += '<ul class="ase-test-results-list">';

            testResults.forEach(function(result) {
                var statusColor = '#46b450';

                if (result.status === 'warning') {
                    statusColor = '#ffb900';
                } else if (result.status === 'failed') {
                    statusColor = '#dc3232';
                }

                html += '<li><span class="ase-status-dot" style="color: ' + statusColor + ';">●</span>';
                html += '<strong>' + result.name + ':</strong> ' + result.message + '</li>';
            });

            html += '</ul>';
            html += '<p class="description"><em>' + t('testNote', 'Note: Server-side tests require the plugin to be fully activated and functional.') + '</em></p>';

            $output.html(html);
            $resultsContainer.show();

            $button.prop('disabled', false).text(defaultText);
        });
    }
})(jQuery);