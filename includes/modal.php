<div id="cfaModal" class="cfa-modal">
    <div class="cfa-modal-content">
        <div class="cfa-modal-header">
            <span id="cfaClose" class="cfa-close">&times;</span>
            <h2>Content Freeze Alert</h2>
        </div>
        <div class="cfa-modal-body">
            <?php echo wp_kses_post(get_option('cfa_custom_message', 'To modify this content freeze alert, please go to Settings --> Content Freeze Alert')); ?>
            <div class="cfa-checkbox-wrapper">
                <label class="cfa-checkbox">
                    <input type="checkbox" id="cfaHideCheckbox"> Hide for 24 hours
                </label>
            </div>
        </div>


        <div class="cfa-modal-footer">
            <button id="cfaCloseBtn">Close</button>
        </div>
    </div>
</div>