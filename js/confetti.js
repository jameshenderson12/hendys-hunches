function launchConfetti() {
    // Start the confetti animation
    $('.confetti-container > span').delay(5000).fadeTo(12000, 0.7);
    console.log("Confetti started");
    addConfettiForDuration(5000); // Adjust the duration here
}

function addConfettiForDuration(duration) {
    var startTime = new Date().getTime();

    var interval = setInterval(function() {
        addNewConfetti();
        var currentTime = new Date().getTime();
        if (currentTime - startTime >= duration) {
            clearInterval(interval); // Stop adding confetti after the specified duration
        }
    }, 100); // Adjust the interval as needed
}

var j = 1;

function addNewConfetti() {
    j++;

    var randomDuration = Math.floor(Math.random() * 5 + 2) * 1000; // * 10 + 6
    $(".confetti-container").append(
        '<div style="border-radius:' + Math.floor(Math.random() * 6 + 1) + 'px;left: ' +
        Math.floor(Math.random() * 100 + 1) +
        "." +
        Math.floor(Math.random() * 100 + 1) +
        "vw; background-color: " +
        "#" +
        Math.random()
            .toString(16)
            .slice(2, 8) +
        "; animation-duration: " +
        randomDuration +
        'ms" class="element"></div>'
    );
    $('.element').animate(
        { top: "115vh", textIndent: Math.floor(Math.random() * 1300) + 600 },
        {
            step: function(now, fx) {
                $(this).css('-webkit-transform', 'rotateX(' + now + 'deg) rotateY(' + now + 'deg) rotateZ(' + now + 'deg)');
            },
            duration: randomDuration,
            easing: 'linear'
        }
    );

    $(".element").animate({ opacity: "0" }, 100, function() {
        $(this).remove();
    });
}