<?php
//<!--Modal Compatencias inicio-->
$modalcancelacion='
  <div id="cancelacion" class="w3-modal">
    <div class="w3-modal-content w3-card-4 w3-animate-zoom">
      <div class="w3-container w3-light-grey">
        <h2>¿Estas seguro de cancelar tu solicitud?</h2>
      </div>
      <form id="formatocaja" class="w3-container"  method="POST" action="cancelacioncaja.php">
      <input type="hidden" name="userid" value="'.$USER->id.'">
      <input type="hidden" name="idcomentario" value="'.$idcomentario.'">
        <p>      
        <label class="w3-text-tiii"><b>Comentarios</b></label>
        <textarea class="mitextarea" rows="5" cols="100%" maxlength="500" name="comentarios" required></textarea>
        </p>
        <div class="w3-container w3-row w3-padding">
          <div class="w3-col w3-left-align s6">
            <p><button type="reset" class="w3-btn w3-green" id="cancelarcaja">Regresar</button></p>
          </div>
          <div class="w3-col w3-right-align s6">
              <input type="submit" id="btncancelacion" name="btncancelacion" class="w3-btn w3-red" value="Cancelar solicitud">
          </div>
        </div>
             </form>
    </div>
  </div>';

  $modalatendido='
  <div id="atendido" class="w3-modal">
    <div class="w3-modal-content w3-card-4 w3-animate-zoom">
      <div class="w3-container w3-light-grey">
        <h2>Registrar comentario</h2>
      </div>
      <form id="atencioncaja" class="w3-container"  method="POST" action="atendidocaja.php">
      <input type="hidden" name="userid" value="'.$USER->id.'">
      <input type="hidden" name="idcomentario" value="'.$idcomentario.'">
        <p>      
        <label class="w3-text-tiii"><b>Comentarios</b></label>
        <textarea class="mitextarea" rows="5" cols="100%" maxlength="500" name="comentarios" required></textarea>
        </p>
        <p>
        <label class="w3-text-tiii"><b>Estatus</b></label>
        <select name="idestatus" class="w3-select" required>
            <option value="" disabled selected>Selecciona una opción</option>
            <option value="2">Atendido</option>
            <option value="3">Cancelado</option>
         </select>
    </p>
        <div class="w3-container w3-row w3-padding">
          <div class="w3-col w3-left-align s6">
            <p><button type="reset" class="w3-btn w3-green" id="cancelar">Cancelar</button></p>
          </div>
          <div class="w3-col w3-right-align s6">
              <input type="submit" id="btnatendido" name="btnatendido" class="w3-btn w3-red" value="Registrar comentario">
          </div>
        </div>
             </form>
    </div>
  </div>';