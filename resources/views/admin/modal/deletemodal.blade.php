<div id="deleteConfirmModal" class="modal fade">
    <div class="modal-dialog modal-confirm">
      <div class="modal-content">
        <div class="modal-header">
          <div class="icon-box">
            <i class="material-icons">&#xE5CD;</i>
          </div>
          <h4 class="modal-title">{{ $title ?? 'Are you sure?' }}</h4>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
          {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}

        </div>
        <div class="modal-body">
          <p>{{ $message ?? 'Do you really want to delete these?' }}</p>
        </div>
        <div class="modal-footer">
                <button type="button" class="btn btn-info" data-bs-dismiss="modal">Cancel</button>
            <div>
                <form id="deleteForm" method="POST" class="d-inline m-0 p-0" style="width: 0px; height:0px; background:none; border:none;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" id="confirmButton">{{ $confirmText ?? 'Delete' }}</button>
                </form>
            </div>
        </div>
      </div>
    </div>
  </div>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>

    .modal-confirm {
      color: #636363;
      width: 330px;
      margin: auto;
      margin-top: 20px;
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
      flex-wrap: nowrap;
    }
    .modal-confirm .modal-footer a {
      color: #999;
    }
    .modal-confirm .icon-box {
      width: 80px;
      height: 80px;
      margin: 0 auto;
      border-radius: 50%;
      z-index: 9;
      text-align: center;
      border: 3px solid #f15e5e ;
    }
    .modal-confirm .icon-box i {
      color: #f15e5e;
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
    .modal-confirm .btn-info {
      background: #c1c1c1;
    }
    .modal-confirm .btn-info:hover, .modal-confirm .btn-info:focus {
      background: #a8a8a8;
    }
    .modal-confirm .btn-danger {
      background: #f15e5e;
    }
    .modal-confirm .btn-danger:hover, .modal-confirm .btn-danger:focus {
      background: #ee3535;
    }
</style>