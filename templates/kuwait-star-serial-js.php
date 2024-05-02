<script>
    function cpKSCode(element) {
        const textToCopy = document.getElementById(element);
        textToCopy.select();
        try {
            // Attempt to copy the selected text to the clipboard
            document.execCommand('copy');
            console.log('Text copied to clipboard');
        } catch (err) {
            console.error('Failed to copy text: ', err);
        }
        // Deselect the text
        window.getSelection().removeAllRanges();
        return false;
    }
    // const shareDialog = document.querySelector('.share-dialog');
    const closeButton = document.querySelector('.close-button');

    function shareKSCode(code) {
        if (navigator.share) {
            navigator.share({
                title: '<?php _e('Share',SPWKS_TD); ?>>',
                url: code
            }).then(() => {
                console.log('Thanks for sharing!');
            })
                .catch(console.error);
        } else {
            // shareDialog.classList.add('is-open');
        }
        return false;
    }
/*
    closeButton.addEventListener('click', event => {
        shareDialog.classList.remove('is-open');
    });*/

</script>