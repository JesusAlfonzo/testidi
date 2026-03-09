<div class="modal fade" id="confirmActionModal" tabindex="-1" role="dialog" aria-labelledby="confirmActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="confirmActionModalLabel">
                    <i class="fas fa-question-circle"></i> Confirmar Acción
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="confirmActionMessage">¿Está seguro de realizar esta acción?</p>
                <div id="confirmActionAlert" class="alert alert-warning" role="alert" style="display:none;"></div>
                <div id="confirmActionInput" style="display:none;">
                    <label id="confirmActionLabel" for="confirmActionReason"></label>
                    <textarea id="confirmActionReason" class="form-control" rows="3" placeholder="Ingrese el motivo..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" id="confirmActionBtn" class="btn btn-primary">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmAction(config) {
    const defaults = {
        title: 'Confirmar Acción',
        message: '¿Está seguro de realizar esta acción?',
        alert: null,
        showInput: false,
        inputLabel: 'Motivo',
        inputRequired: true,
        confirmBtnText: 'Confirmar',
        confirmBtnClass: 'btn-primary',
        onConfirm: null
    };
    
    const options = { ...defaults, ...config };
    
    $('#confirmActionModalLabel').html('<i class="fas fa-question-circle"></i> ' + options.title);
    $('#confirmActionMessage').text(options.message);
    
    if (options.alert) {
        $('#confirmActionAlert').html('<strong>' + options.alert + '</strong>').show();
    } else {
        $('#confirmActionAlert').hide();
    }
    
    if (options.showInput) {
        $('#confirmActionLabel').text(options.inputLabel);
        $('#confirmActionReason').prop('required', options.inputRequired);
        $('#confirmActionInput').show();
    } else {
        $('#confirmActionInput').hide();
    }
    
    const $confirmBtn = $('#confirmActionBtn');
    $confirmBtn.text(options.confirmBtnText);
    $confirmBtn.removeClass('btn-primary btn-danger btn-warning btn-success').addClass(options.confirmBtnClass);
    
    $confirmBtn.off('click').on('click', function() {
        if (options.showInput && options.inputRequired) {
            const reason = $('#confirmActionReason').val().trim();
            if (!reason) {
                $('#confirmActionReason').focus();
                return;
            }
            if (options.onConfirm) options.onConfirm(reason);
        } else {
            if (options.onConfirm) options.onConfirm();
        }
        $('#confirmActionModal').modal('hide');
    });
    
    $('#confirmActionModal').modal('show');
}
</script>
