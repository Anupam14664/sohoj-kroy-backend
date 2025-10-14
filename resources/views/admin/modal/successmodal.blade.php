<div id="successModal" class="modal fade">
    <div class="modal-dialog modal-confirm">
      <div class="modal-content">
        <div class="modal-header">
          <div class="icon-box1" style="border-color: #47c9a2;">
            <i class="material-icons">&#xE5CA;</i>
          </div>
          <h4 class="modal-title">{{ $title ?? 'Success!' }}</h4>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
          <p>{{ $message ?? 'Operation completed successfully.' }}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <style>
    .modal-confirm {
      color: #636363;
      width: 330px;
      margin: auto
    }
    .modal-confirm .modal-content {
      padding: 20px;
      border-radius: 5px;
      border: none;
      text-align: center;
      font-size: 14px;
      top: 100;
    }
    .modal-confirm .modal-header {
      border-bottom: none;
      position: relative;
      display: block;
    }
    .modal-confirm h4 {
      text-align: center;
      font-size: 26px;
      margin: 30px 0 -10px;
    }
    .modal-confirm .close {
      position: absolute;
      top: -5px;
      right: -2px;
    }
    .modal-confirm .modal-body {
      color: #999;
    }
    .modal-confirm .modal-footer {
      border: none;
      text-align: center;
      border-radius: 5px;
      font-size: 13px;
      padding: 10px 12px 25px;
    }
    .modal-confirm .modal-footer a {
      color: #999;
    }
    .modal-confirm .icon-box1 {
      width: 80px;
      height: 80px;
      margin: 0 auto;
      border-radius: 50%;
      z-index: 9;
      text-align: center;
      border: 3px solid #47c9a2;
    }
    .modal-confirm .icon-box1 i {
      color: #47c9a2;
      font-size: 46px;
      display: inline-block;
      margin-top: 13px;
    }
    .modal-confirm .btn {
      color: #fff;
      border-radius: 4px;
      background: #60c7c1;
      text-decoration: none;
      transition: all 0.4s;
      line-height: normal;
      min-width: 120px;
      border: none;
      min-height: 40px;
      border-radius: 3px;
      margin: 0 5px;
      outline: none !important;
    }
    .modal-confirm .btn-success {
      background: #47c9a2;
    }
    .modal-confirm .btn-success:hover, .modal-confirm .btn-success:focus {
      background: #2fb38a;
    }
  </style>