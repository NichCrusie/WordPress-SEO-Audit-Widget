jQuery(document).ready(function($) {

    // Function to animate typing dots
    function animateTypingDots(element, numDots, callback) {
        var dots = '';
        var count = 0;
        var typingInterval = setInterval(function() {
            if (count < numDots) {
                dots += '.';
                element.html(dots);
                count++;
            } else {
                clearInterval(typingInterval);
                if (callback) {
                    callback();
                }
            }
        }, 300); // Slightly faster interval for typing dots
    }

    // Function to animate audit items
    function animateAuditItems(items, index, callback) {
        if (index < items.length) {
            var currentItem = items.eq(index);
            var checkName = currentItem.data('check-name');
            var statusClass = currentItem.hasClass('passed') ? 'passed' : 'failed';
            var statusText = currentItem.hasClass('passed') ? 'Passed' : 'Failed';

            // Store original styles
            var originalBackgroundColor = currentItem.css('background-color');
            var originalTextColor = currentItem.css('color');

            // Calculate dimensions of "Checking..." message
            var tempContainer = currentItem.clone().css({
                'visibility': 'hidden',
                'position': 'absolute',
                'width': currentItem.width(),
                'height': currentItem.height()
            });
            $('body').append(tempContainer);
            var checkingWidth = tempContainer.find('.checking-text').width();
            var checkingHeight = tempContainer.find('.checking-text').height();
            tempContainer.remove();

            // Apply temporary styles for checks
            currentItem.css({
                'color': '#000',
                'background-color': 'transparent'
            });

            // Display "Checking..." message
            currentItem.fadeOut(200, function() {
                currentItem.html('<span class="checking-text">Checking ' + checkName + '</span><span class="typing-dots"></span>').fadeIn(200, function() {
                    // Animate typing effect for dots
                    animateTypingDots(currentItem.find('.typing-dots'), 3, function() {
                        // Display result after typing effect
                        currentItem.fadeOut(200, function() {
                            currentItem.html('<span class="checking-text">' + checkName + ': ' + statusText + '</span>').removeClass().addClass(statusClass);
                            currentItem.css({
                                'background-color': originalBackgroundColor,
                                'color': originalTextColor,
                                'width': checkingWidth,
                                'height': checkingHeight
                            });

                            // Display result after delay with fade in
                            currentItem.delay(0).fadeIn(0, function() {
                                // Move to the next item
                                animateAuditItems(items, index + 1, callback);
                            });
                        });
                    });
                });
            });
        } else {
            // Call the provided callback function when all items are animated
            if (callback) {
                callback();
            }
        }
    }
    
    // Start animation when the page loads
    animateAuditItems($('.seo-audit-list li'), 0, function() {
        // All audit items animation is complete
    });
    
});
