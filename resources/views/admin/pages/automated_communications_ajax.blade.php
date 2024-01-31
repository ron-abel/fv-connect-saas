@php $all_selected_cat_ids=[]; @endphp
    @foreach($auto_note_phases as $single_note_phase)
        <div class="row ml-3 mr-0 mt-3 dv-webhook-row" id="id_webhook_{{$single_note_phase->id }}" phaseId="{{$single_note_phase->fv_phase_id}}">
            <div class="col-sm-12 px-1">
                <div class="form-group mt-10">
                    <div class="row mx-0 phase_form table_row" fv_phase_id="{{$single_note_phase->fv_phase_id}}">
                        <!-- <div class="col-sm-2 px-1 custom-input">
                            <label for="">If Phase Change</label>
                            <select name="phase_change_type" id="" value="{{ $single_note_phase->phase_change_type }}" class="form-control phase_form_select" data-id="phase_form">
                            <option @if($single_note_phase->phase_change_type == "Equals Exactly") selected="selected" @endif value="Equals Exactly">Equals Exactly</option>
                            </select>
                        </div> -->
                        <input type="hidden" name="project_type_id" id=""  class="form-control project_type" value="{{ $single_note_phase->fv_project_type_id }}">
                        <div class="col-sm-2 px-1 ml-5 custom-input">
                            <label for="">Phase Change</label>
                            <select name="fv_phase_id" onchange="changeHiddenPhase(this)" class="form-control fv_phase_id" required >
                                <option value="" selected="selected">--Select Phase--</option>
                                @if(count($project_type_phases) > 0)
                                    @foreach ($project_type_phases as $phase)
                                    @php
                                        if(in_array($phase['name'], $auto_note_phases_Array) && $single_note_phase->phase_name != $phase['name']) {
                                            continue;
                                        }
                                        $selected = "";
                                        if($single_note_phase->phase_name == $phase['name']) $selected="selected";
                                    @endphp
                                    <option {{$selected}} value="{{ $phase['phaseId']['native'] }}">
                                        {{ $phase['name'] }}
                                    </option>
                                    @endforeach
                                @endif
                            </select>
                            <input name="phase_change_event" type="hidden" value="{{ $single_note_phase->phase_name }}" class="form-control phase_change_event">
                        </div>
                        <div class="col-sm-2 px-1 ml-5 custom-input">
                            <label for="">Enable/Disable</label>
                            <select name="phase_change_enable" id="" value="" class="form-control phase_change_enable">
                                <option @if($single_note_phase->is_active == 1) selected="selected" @endif value="1">Enable</option>
                                <option @if($single_note_phase->is_active == 0) selected="selected" @endif value="0">Disable</option>
                            </select>
                        </div>
                        <div class="col-sm-3 px-1 ml-5">
                            <label for="" style="display:{{ ($single_note_phase->is_active == 0) ? 'none' : 'block' }}">Text Message</label>
                            <textarea style="display:{{ ($single_note_phase->is_active == 0) ? 'none' : 'block' }}" class="form-control" name="custom_message" id="txtDescription_0" cols="60" rows="5" required="" spellcheck="true">{{ $single_note_phase->custom_message }}</textarea>
                        </div>
                        <div class="col-sm-1 px-1 ml-5">
                            <label for="">Review? <span class="fas fa-exclamation-circle" data-toggle="tooltip" title="" data-original-title="Check to send Google Review on this Phase Change."></span></label>
                            <input type="checkbox" class="form-control goog-check" @if($single_note_phase->is_send_google_review == 1) checked @endif name="is_send_google_review">
                        </div>
                        <div class="col-sm-1 px-1 mt-6 ml-5">
                            <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save" data-id="{{ $single_note_phase->id }}" style="float:left;">Save</button>
                        </div>
                        <div class="col-sm-1 px-1 mt-6">
                            <button class="btn ml-auto mt-1 btn-danger btn-md delete" data-id="{{ $single_note_phase->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
                        </div>

                        <div class="process-result col-sm-3 px-1 mt-10"></div>
                    </div>
                </div>
            </div>
        </div>
        @php  $all_selected_cat_ids[] = $single_note_phase->fv_phase_id; @endphp
    @endforeach
<input type='hidden' name='AllSelectedCatIds' id='AllSelectedCatIds' value="{{ implode(',', $all_selected_cat_ids) }}">
<?php
    $html = '<select name="fv_phase_id" onchange="changeHiddenPhase(this)" class="form-control fv_phase_id" required>';
    $html .= '<option value="" selected="selected">--Select Phase--</option>';
    // foreach ($project_type_phases as $phase) {
    //     $html .= '<option  value="'.$phase['phaseId']['native'].'">'.$phase['name'].'</option>';
    // }
    $html .= "</select>";
    $projectType = [];
    $projectTypeHtml ='<option value="0" selected="selected">--Select Phase--</option>';

?>

<script>
  var projectType = <?php echo json_encode($projectType); ?>;
  var phaseHtml = `<?php echo $html ?>`;
  var projectTypeHtml = `<?php echo $projectTypeHtml ?>`;
  var current_project_id = `<?php echo $current_project_typeid; ?>`;
</script>
