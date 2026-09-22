<script src="https://evdemon.org/blog/vendor/rmm5t/jquery-timeago/jquery.timeago.js"></script>
<script src="https://evdemon.org/blog/vendor/rmm5t/jquery-timeago/locales/jquery.timeago.en.js"></script>

<script>
(function() {
    var badPhrases = ['access denied', '403 forbidden', 'forbidden', 'not found', '404', 'error'];

    function checkAndHide(block) {
        var titleLink = block.querySelector('.unfurled-url h3 a');
        if (titleLink) {
            var text = titleLink.textContent.trim().toLowerCase();
            if (badPhrases.some(function(p) { return text.includes(p); })) {
                block.style.display = 'none';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.unfurl-block').forEach(checkAndHide);

        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        if (node.classList && node.classList.contains('unfurl-block')) {
                            checkAndHide(node);
                        }
                        var blocks = node.querySelectorAll ? node.querySelectorAll('.unfurl-block') : [];
                        blocks.forEach(checkAndHide);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    });
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.h-entry').forEach(function(entry) {
        var permalinkEl = entry.querySelector('.permalink a.u-url');
        if (!permalinkEl) return;
        var baseUrl = permalinkEl.href;
        var counter = 0;

        entry.querySelectorAll('.entry-content h3, .e-content h3').forEach(function(el) {
            counter++;
            var anchorId = 'p' + counter;
            el.id = anchorId;
            var link = document.createElement('a');
            link.href = baseUrl + '#' + anchorId;
            link.className = 'heading-permalink';
            link.innerHTML = el.innerHTML;
            el.innerHTML = '';
            el.appendChild(link);
        });

        entry.querySelectorAll('.entry-content p, .e-content p').forEach(function(el) {
            var textOnly = el.textContent.trim();
            var hasImg = el.querySelector('img');
            if (textOnly === '' && hasImg) {
                return;
            }
            counter++;
            var anchorId = 'p' + counter;
            el.id = anchorId;
            var link = document.createElement('a');
            link.href = baseUrl + '#' + anchorId;
            link.className = 'para-permalink';
            link.title = 'Direct link to this paragraph';
            link.textContent = '#';
            el.appendChild(document.createTextNode(' '));
            el.appendChild(link);
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    function showRotateModal(url, onDone) {
        var overlay = document.createElement('div');
        overlay.className = 'simple-rte-rotate-overlay';

        var box = document.createElement('div');
        box.className = 'simple-rte-rotate-box';

        var img = document.createElement('img');
        img.src = url;
        img.className = 'simple-rte-rotate-preview';

        var buttonRow = document.createElement('div');
        buttonRow.className = 'simple-rte-rotate-buttons';
        buttonRow.innerHTML =
            '<button type="button" data-angle="-90">Rotate Left 90&deg;</button>' +
            '<button type="button" data-angle="90">Rotate Right 90&deg;</button>' +
            '<button type="button" data-angle="180">Rotate 180&deg;</button>';

        var doneRow = document.createElement('div');
        doneRow.className = 'simple-rte-rotate-done-row';
        doneRow.innerHTML = '<button type="button" class="simple-rte-rotate-finish">Use This Photo</button>';

        box.appendChild(img);
        box.appendChild(buttonRow);
        box.appendChild(doneRow);
        overlay.appendChild(box);
        document.body.appendChild(overlay);

        function rotate(angle) {
            var filename = url.split('/').pop();
            buttonRow.querySelectorAll('button').forEach(function(b) { b.disabled = true; });

            fetch('https://evdemon.org/blog/rotate-photo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ filename: filename, angle: angle }),
                credentials: 'same-origin'
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                buttonRow.querySelectorAll('button').forEach(function(b) { b.disabled = false; });
                if (result.error) {
                    alert('Rotation failed: ' + result.error);
                    return;
                }
                img.src = url + '?t=' + Date.now();
            })
            .catch(function() {
                buttonRow.querySelectorAll('button').forEach(function(b) { b.disabled = false; });
                alert('Rotation failed. Please try again.');
            });
        }

        buttonRow.querySelectorAll('button').forEach(function(btn) {
            btn.addEventListener('click', function() {
                rotate(parseInt(btn.dataset.angle, 10));
            });
        });

        doneRow.querySelector('.simple-rte-rotate-finish').addEventListener('click', function() {
            document.body.removeChild(overlay);
            onDone();
        });
    }

    function initRichText(textarea) {
        if (textarea.dataset.customRichText) return;
        textarea.dataset.customRichText = 'true';

        var quickSubmit = textarea.classList.contains('ctrl-enter-submit');
        var isPostEditor = textarea.classList.contains('wysiwyg');

        var toolbar = document.createElement('div');
        toolbar.className = 'simple-rte-toolbar';
        toolbar.innerHTML =
            '<button type="button" data-cmd="bold"><b>B</b></button>' +
            '<button type="button" data-cmd="italic"><i>I</i></button>' +
            '<button type="button" data-cmd="formatBlock" data-value="h3">H3</button>' +
            '<button type="button" data-cmd="formatBlock" data-value="blockquote">Quote</button>' +
            '<button type="button" data-cmd="formatBlock" data-value="p">Para</button>' +
            '<button type="button" data-cmd="createLink">Link</button>' +
            '<button type="button" data-cmd="insertUnorderedList">List</button>' +
            '<button type="button" data-cmd="insertImage">Image</button>' +
            '<button type="button" class="simple-rte-photo-btn">Photo</button>' +
            '<input type="file" accept="image/*" capture="environment" class="simple-rte-photo-input" style="display:none;">' +
            '<button type="button" class="simple-rte-html-toggle">&lt;/&gt;</button>' +
            '<button type="button" class="simple-rte-help-btn">?</button>' +
            '<div class="simple-rte-help-popup" style="display:none;">' +
                '<strong>Keyboard shortcuts</strong><br>' +
                'Ctrl+B: Bold<br>' +
                'Ctrl+I: Italic<br>' +
                'Ctrl+K: Link<br>' +
                'Ctrl+Shift+.: Blockquote (press again to exit)<br>' +
                'Ctrl+Alt+3: Heading 3<br>' +
                'Ctrl+Shift+8: Bullet list' +
            '</div>';

        var editable = document.createElement('div');
        editable.className = 'simple-rte-editable';
        editable.contentEditable = 'true';
        editable.innerHTML = textarea.value;

        var htmlView = document.createElement('textarea');
        htmlView.className = 'simple-rte-htmlview';
        htmlView.style.display = 'none';

        textarea.style.display = 'none';
        textarea.parentNode.insertBefore(toolbar, textarea);
        textarea.parentNode.insertBefore(editable, textarea);
        textarea.parentNode.insertBefore(htmlView, textarea);

        function sync() {
            if (htmlView.style.display === 'none') {
                textarea.value = editable.innerHTML;
            } else {
                textarea.value = htmlView.value;
            }
        }

        function findEnclosingBlockquote() {
            var sel = window.getSelection();
            if (!sel.rangeCount) return null;
            var node = sel.getRangeAt(0).commonAncestorContainer;
            if (node.nodeType === 3) node = node.parentNode;
            return node.closest ? node.closest('blockquote') : null;
        }

        function toggleBlockquote() {
            var bq = findEnclosingBlockquote();
            if (bq) {
                var p = document.createElement('p');
                p.innerHTML = '<br>';
                bq.parentNode.insertBefore(p, bq.nextSibling);
                var range = document.createRange();
                range.setStart(p, 0);
                range.collapse(true);
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            } else {
                document.execCommand('formatBlock', false, 'blockquote');
            }
        }

        function cleanEmptyBlocks() {
            var blocks = editable.querySelectorAll('div, p');
            blocks.forEach(function(el) {
                var content = el.innerHTML.trim().toLowerCase();
                if (content === '' || content === '<br>' || content === '&nbsp;') {
                    el.remove();
                }
            });
        }

        editable.addEventListener('input', sync);
        editable.addEventListener('blur', sync);
        htmlView.addEventListener('input', sync);
        htmlView.addEventListener('blur', sync);

        editable.addEventListener('keydown', function(e) {
            var mod = e.ctrlKey || e.metaKey;

            if (mod && e.key === 'Enter' && quickSubmit) {
                e.preventDefault();
                cleanEmptyBlocks();
                sync();
                var form = textarea.closest('form');
                if (form) {
                    if (form.requestSubmit) {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }
                return;
            }

            if (!mod) return;

            if (e.key === 'b') {
                e.preventDefault();
                document.execCommand('bold', false, null);
                sync();
            } else if (e.key === 'i') {
                e.preventDefault();
                document.execCommand('italic', false, null);
                sync();
            } else if (e.key === 'k') {
                e.preventDefault();
                var url = prompt('Enter URL:', 'https://');
                if (url) document.execCommand('createLink', false, url);
                sync();
            } else if (e.shiftKey && e.code === 'Period') {
                e.preventDefault();
                toggleBlockquote();
                sync();
            } else if (e.altKey && e.key === '3') {
                e.preventDefault();
                document.execCommand('formatBlock', false, 'h3');
                sync();
            } else if (e.shiftKey && e.code === 'Digit8') {
                e.preventDefault();
                document.execCommand('insertUnorderedList', false, null);
                sync();
            }
        });

        var htmlToggle = toolbar.querySelector('.simple-rte-html-toggle');
        var formatButtons = toolbar.querySelectorAll('button:not(.simple-rte-html-toggle):not(.simple-rte-help-btn):not(.simple-rte-photo-btn)');

        htmlToggle.addEventListener('click', function(e) {
            e.preventDefault();
            var switchingToHtml = (htmlView.style.display === 'none');
            if (switchingToHtml) {
                htmlView.value = editable.innerHTML;
                editable.style.display = 'none';
                htmlView.style.display = 'block';
                formatButtons.forEach(function(b) { b.disabled = true; });
                htmlToggle.classList.add('active');
                htmlView.focus();
            } else {
                editable.innerHTML = htmlView.value;
                htmlView.style.display = 'none';
                editable.style.display = 'block';
                formatButtons.forEach(function(b) { b.disabled = false; });
                htmlToggle.classList.remove('active');
            }
            sync();
        });

        var helpBtn = toolbar.querySelector('.simple-rte-help-btn');
        var helpPopup = toolbar.querySelector('.simple-rte-help-popup');
        helpBtn.addEventListener('click', function(e) {
            e.preventDefault();
            helpPopup.style.display = (helpPopup.style.display === 'none') ? 'block' : 'none';
        });

        var photoBtn = toolbar.querySelector('.simple-rte-photo-btn');
        var photoInput = toolbar.querySelector('.simple-rte-photo-input');

        function insertPhotoImg(url) {
            showRotateModal(url, function() {
                editable.focus();
                var floatChoice = prompt('Float: left, right, or none?', 'none');
                var cls = floatChoice === 'left' ? 'img-float-left' : (floatChoice === 'right' ? 'img-float-right' : '');
                var width = prompt('Width in px (leave blank for full size):', '');
                var styleAttr = width ? ' style="width:' + width + 'px;"' : '';
                document.execCommand('insertHTML', false, '<img class="' + cls + '"' + styleAttr + ' src="' + url + '?t=' + Date.now() + '">');
                sync();
            });
        }

        photoBtn.addEventListener('click', function(e) {
            e.preventDefault();
            photoInput.click();
        });

        photoInput.addEventListener('change', function() {
            if (!photoInput.files || !photoInput.files[0]) return;

            var originalLabel = photoBtn.textContent;
            photoBtn.textContent = 'Uploading...';
            photoBtn.disabled = true;

            var formData = new FormData();
            formData.append('photo', photoInput.files[0]);

            fetch('https://evdemon.org/blog/simple-upload.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                photoBtn.textContent = originalLabel;
                photoBtn.disabled = false;
                photoInput.value = '';

                if (data.error) {
                    alert('Upload failed: ' + data.error);
                    return;
                }

                insertPhotoImg(data.url);
            })
            .catch(function(err) {
                photoBtn.textContent = originalLabel;
                photoBtn.disabled = false;
                alert('Upload failed: ' + err.message);
            });
        });

        formatButtons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                editable.focus();
                var cmd = btn.dataset.cmd;
                if (cmd === 'createLink') {
                    var url = prompt('Enter URL:', 'https://');
                    if (url) document.execCommand('createLink', false, url);
                } else if (cmd === 'insertImage') {
                    var imgUrl = prompt('Enter image URL:', 'https://');
                    if (imgUrl) {
                        var floatChoice = prompt('Float: left, right, or none?', 'none');
                        var cls = floatChoice === 'left' ? 'img-float-left' : (floatChoice === 'right' ? 'img-float-right' : '');
                        var width = prompt('Width in px (leave blank for full size):', '');
                        var styleAttr = width ? ' style="width:' + width + 'px;"' : '';
                        document.execCommand('insertHTML', false, '<img class="' + cls + '"' + styleAttr + ' src="' + imgUrl + '">');
                    }
                } else if (cmd === 'formatBlock' && btn.dataset.value === 'blockquote') {
                    toggleBlockquote();
                } else if (btn.dataset.value) {
                    document.execCommand(cmd, false, btn.dataset.value);
                } else {
                    document.execCommand(cmd, false, null);
                }
                sync();
            });
        });

        var form = textarea.closest('form');
        if (form) {
            var isNewPostForm = isPostEditor && /\/entry\/edit\/?($|\?)/.test(form.action);

            function handleAppendFlow() {
                fetch('https://evdemon.org/blog/list-recent-posts.php', { credentials: 'same-origin' })
                    .then(function(r) { return r.json(); })
                    .then(function(posts) {
                        if (!posts || posts.error || !posts.length) {
                            alert('No recent posts found to append to. Publishing as a new post instead.');
                            cleanEmptyBlocks();
                            sync();
                            form.requestSubmit();
                            return;
                        }

                        var menu = 'Pick a post to append to:\n';
                        posts.forEach(function(p, i) {
                            menu += (i + 1) + '. ' + p.title + '\n';
                        });
                        var choice = prompt(menu, '1');
                        var idx = parseInt(choice, 10) - 1;

                        if (isNaN(idx) || idx < 0 || idx >= posts.length) {
                            alert('Invalid selection. Publishing as a new post instead.');
                            cleanEmptyBlocks();
                            sync();
                            form.requestSubmit();
                            return;
                        }

                        var target = posts[idx];

                        cleanEmptyBlocks();
                        var newContent = editable.innerHTML;
                        var sourceTitleInput = form.querySelector('input[name="title"]');
                        var sourceTitle = sourceTitleInput ? sourceTitleInput.value.trim() : '';

                        fetch('https://evdemon.org/blog/append-to-post.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                uuid: target.uuid,
                                content: newContent,
                                title: sourceTitle
                            }),
                            credentials: 'same-origin'
                        })
                        .then(function(r) {
                            return r.text().then(function(text) {
                                var data;
                                try {
                                    data = JSON.parse(text);
                                } catch (e) {
                                    throw new Error('AMBIGUOUS_RESPONSE');
                                }
                                return data;
                            });
                        })
                        .then(function(result) {
                            if (result.error) {
                                alert('Append failed: ' + result.error + '. Publishing as a new post instead.');
                                cleanEmptyBlocks();
                                sync();
                                form.requestSubmit();
                                return;
                            }
                            window.location.href = result.url;
                        })
                        .catch(function(err) {
                            if (err.message === 'AMBIGUOUS_RESPONSE') {
                                alert('The server response was unclear. The content may have already been appended successfully. Please check your posts before trying again \u2014 a new post will NOT be created automatically to avoid duplicating content.');
                            } else {
                                alert('Could not reach the server: ' + err.message + '. Please check your posts before trying again \u2014 a new post will NOT be created automatically to avoid duplicating content.');
                            }
                        });
                    })
                    .catch(function() {
                        alert('Could not check for recent posts. Publishing as a new post instead.');
                        cleanEmptyBlocks();
                        sync();
                        form.requestSubmit();
                    });
            }

            form.addEventListener('submit', function(evt) {
                if (isNewPostForm && !form.dataset.appendChoiceMade) {
                    var wantsAppend = confirm('Append this content to an existing post instead of creating a new one?');
                    form.dataset.appendChoiceMade = 'true';

                    if (wantsAppend) {
                        evt.preventDefault();
                        handleAppendFlow();
                        return;
                    }

                    cleanEmptyBlocks();
                    sync();
                    return;
                }

                cleanEmptyBlocks();
                sync();
            });
        }
    }

    document.querySelectorAll('textarea.bodyInput').forEach(initRichText);

    var rteObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType !== 1) return;
                if (node.matches && node.matches('textarea.bodyInput')) {
                    initRichText(node);
                }
                if (node.querySelectorAll) {
                    node.querySelectorAll('textarea.bodyInput').forEach(initRichText);
                }
            });
        });
    });
    rteObserver.observe(document.body, { childList: true, subtree: true });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function matchLinkblogHeight() {
        var aboutBox = document.querySelector('.about-sidebar');
        var linkblogBox = document.querySelector('.blogroll-sidebar');
        if (aboutBox && linkblogBox && window.innerWidth >= 992) {
            var aboutHeight = aboutBox.offsetHeight;
            linkblogBox.style.maxHeight = aboutHeight + 'px';
        }
    }
    matchLinkblogHeight();
    window.addEventListener('load', matchLinkblogHeight);
    window.addEventListener('resize', matchLinkblogHeight);
});
</script>
