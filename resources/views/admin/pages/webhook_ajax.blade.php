@foreach($webhooks as $webhook_obj)
   @php $webhook_settings_count_parse[$webhook_obj->trigger_action_name] = 1; @endphp
   <div class="row ml-3 mr-0 mt-3 dv-webhook-row {{ $webhook_obj->trigger_action_name }}" id="id_webhook_{{$webhook_obj->id }}">
       <div class="col-sm-12 px-1">
           @if($webhook_obj->trigger_action_name == 'PhaseChanged' && $trigger_action_name=='PhaseChanged')
           <!-- Phase Changed row -->
           <div class="row mx-0 phase_form">
               <div class="col-sm-2 px-1">
                   <label for="">If Phase Change</label>
                   <select name="item_change_type" id="" value="{{ $webhook_obj->item_change_type }}" class="form-control phase_form_select" data-id="phase_form">
                       <option value="Equals Exactly" @if($webhook_obj->item_change_type == "Equals Exactly") selected="selected"
                           @endif >Equals Exactly</option>
                       <!--<option value="Does Not Equal" @if($webhook_obj->item_change_type == "Does Not Equal") selected="selected" @endif >Does Not Equal</option>
                                                            <option value="Is Not" @if($webhook_obj->item_change_type == "Is Not") selected="selected" @endif >Is Not</option>
                                                            <option value="Contains" @if($webhook_obj->item_change_type == "Contains") selected="selected" @endif >Contains</option>-->
                   </select>
               </div>
               <div class="col-sm-3 px-1">
                   <label for="">Phase Change To</label>
                   <input type="text" class="form-control" name="phase_change_event" value="{{ $webhook_obj->phase_change_event }}" />
               </div>
               <div class="col-sm-4 px-1">
                   <label for="">Deliver to Webhook URL Endpoint</label>
                   <input type="text" class="form-control" name="delivery_hook_url" value="{{ $webhook_obj->delivery_hook_url }}" />
               </div>
               <div class="col-sm-1 px-1 mt-6">
                   <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save" data-id="{{ $webhook_obj->id }}" style="float:left;">SAVE</button>
               </div>
               <div class="col-sm-1 px-1 mt-6">
                   <button class="btn ml-auto mt-1 btn-danger btn-md delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
               </div>
               <div class="process-result col-sm-3 px-1 mt-10"></div>
           </div>

           @elseif($webhook_obj->trigger_action_name == 'CollectionItemCreated' && $trigger_action_name=='CollectionItemCreated')
           <!-- Phase Changed row -->
           <div class="row mx-0 phase_form">
               <div class="col-sm-2 px-1">
                   <label for="">If Collection</label>
                   <select name="item_change_type" id="" value="{{ $webhook_obj->item_change_type }}" class="form-control phase_form_select" data-id="phase_form">
                       <option value="Equals Exactly" @if($webhook_obj->item_change_type == "Equals Exactly") selected="selected"
                           @endif >Equals Exactly</option>
                   </select>
               </div>
               <div class="col-sm-3 px-1">
                   <label for="">Collection Item Created To</label>
                   <input type="text" class="form-control" name="collection_changed" value="{{ $webhook_obj->collection_changed }}" />
               </div>
               <div class="col-sm-4 px-1">
                   <label for="">Deliver to Webhook URL Endpoint</label>
                   <input type="text" class="form-control" name="delivery_hook_url" value="{{ $webhook_obj->delivery_hook_url }}" />
               </div>
               <div class="col-sm-1 px-1 mt-6">
                   <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save" data-id="{{ $webhook_obj->id }}" style="float:left;">SAVE</button>
               </div>
               <div class="col-sm-1 px-1 mt-6">
                   <button class="btn ml-auto mt-1 btn-danger btn-md delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
               </div>
               <div class="process-result col-sm-3 px-1 mt-10"></div>
           </div>

           @elseif($webhook_obj->trigger_action_name == 'TaskCreated' && $trigger_action_name=='TaskCreated')
           <!-- Phase Changed row -->
           <div class="row mx-0 phase_form">
               <div class="col-sm-2 px-1">
                   <label for="">If Task</label>
                   <select name="item_change_type" id="" value="{{ $webhook_obj->item_change_type }}" class="form-control phase_form_select" data-id="phase_form">
                       <option value="Contains" @if($webhook_obj->item_change_type == "Contains") selected="selected"
                           @endif >Contains</option>
                   </select>
               </div>
               <div class="col-sm-3 px-1">
                   <label for="">Task Created To</label>
                   <input type="text" class="form-control" name="task_changed" value="{{ $webhook_obj->task_changed }}" />
               </div>
               <div class="col-sm-4 px-1">
                   <label for="">Deliver to Webhook URL Endpoint</label>
                   <input type="text" class="form-control" name="delivery_hook_url" value="{{ $webhook_obj->delivery_hook_url }}" />
               </div>
               <div class="col-sm-1 px-1 mt-6">
                   <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save" data-id="{{ $webhook_obj->id }}" style="float:left;">SAVE</button>
               </div>
               <div class="col-sm-1 px-1 mt-6">
                   <button class="btn ml-auto mt-1 btn-danger btn-md delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
               </div>
               <div class="process-result col-sm-3 px-1 mt-10"></div>
           </div>

           @elseif($webhook_obj->trigger_action_name == 'ContactCreated' && $trigger_action_name=='ContactCreated')
           <div class="row mx-0 form-group">
               <div class="col-sm-6 px-1 others_form">
                   <label for="">Destination Webhook URL</label>
                   <input type="text" name="delivery_hook_url" class="form-control" value="{{ $webhook_obj->delivery_hook_url }}" />
               </div>
               <div class="col-sm-1 px-1 mt-3">
                   <button type="submit" class="btn btn-success mt-4 btn-md text-white save" data-id="{{ $webhook_obj->id }}" style="float:left;">SAVE</button>
               </div>
               <div class="col-sm-1 px-1 mt-6">
                   <button class="btn ml-auto mt-1 btn-danger btn-md  delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
               </div>
               <div class="process-result col-sm-2 px-1 mt-10"></div>
           </div>

           @elseif ($webhook_obj->trigger_action_name == 'ProjectCreated' && $trigger_action_name=='ProjectCreated')
           <div class="row mx-0 form-group">
               <div class="col-sm-6 px-1 others_form">
                   <label for="">Destination Webhook URL</label>
                   <input type="text" name="delivery_hook_url" class="form-control" value="{{ $webhook_obj->delivery_hook_url }}" />
               </div>
               <div class="col-sm-1 px-1 mt-3">
                   <button type="submit" class="btn btn-success mt-4 btn-md text-white save" data-id="{{ $webhook_obj->id }}" style="float:left;">SAVE</button>
               </div>
               <div class="col-sm-1 px-1 mt-6">
                   <button class="btn ml-auto mt-1 btn-danger btn-md  delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
               </div>
               <div class="process-result col-sm-2 px-1 mt-10"></div>
           </div>

            @elseif($webhook_obj->trigger_action_name == 'CollectionItemCreated' && $trigger_action_name=='CollectionItemCreated')
            <!-- Phase Changed row -->
            <div class="row mx-0 phase_form">
                <div class="col-sm-2 px-1">
                    <label for="">If Collection</label>
                    <select name="item_change_type" id="" value="{{ $webhook_obj->item_change_type }}" class="form-control phase_form_select" data-id="phase_form">
                        <option value="Equals Exactly" @if($webhook_obj->item_change_type == "Equals Exactly") selected="selected"
                            @endif >Equals Exactly</option>
                    </select>
                </div>
                <div class="col-sm-3 px-1">
                    <label for="">Collection Item Created To</label>
                    <input type="text" class="form-control" name="collection_changed" value="{{ $webhook_obj->collection_changed }}" />
                </div>
                <div class="col-sm-4 px-1">
                    <label for="">Deliver to Webhook URL Endpoint</label>
                    <input type="text" class="form-control" name="delivery_hook_url" value="{{ $webhook_obj->delivery_hook_url }}" />
                </div>
                <div class="col-sm-1 px-1 mt-6">
                    <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save" data-id="{{ $webhook_obj->id }}" style="float:left;">SAVE</button>
                </div>
                <div class="col-sm-1 px-1 mt-6">
                    <button class="btn ml-auto mt-1 btn-danger btn-md delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
                </div>
                <div class="process-result col-sm-3 px-1 mt-10"></div>
            </div>

            @elseif($webhook_obj->trigger_action_name == 'TaskCreated' && $trigger_action_name=='TaskCreated')
            <!-- Phase Changed row -->
            <div class="row mx-0 phase_form">
                <div class="col-sm-2 px-1">
                    <label for="">If Task</label>
                    <select name="item_change_type" id="" value="{{ $webhook_obj->item_change_type }}" class="form-control phase_form_select" data-id="phase_form">
                        <option value="Contains" @if($webhook_obj->item_change_type == "Contains") selected="selected"
                            @endif >Contains</option>
                    </select>
                </div>
                <div class="col-sm-3 px-1">
                    <label for="">Task Created To</label>
                    <input type="text" class="form-control" name="task_changed" value="{{ $webhook_obj->task_changed }}" />
                </div>
                <div class="col-sm-4 px-1">
                    <label for="">Deliver to Webhook URL Endpoint</label>
                    <input type="text" class="form-control" name="delivery_hook_url" value="{{ $webhook_obj->delivery_hook_url }}" />
                </div>
                <div class="col-sm-1 px-1 mt-6">
                    <button type="submit" class="btn ml-auto mt-1 btn-success btn-md save" data-id="{{ $webhook_obj->id }}" style="float:left;">SAVE</button>
                </div>
                <div class="col-sm-1 px-1 mt-6">
                    <button class="btn ml-auto mt-1 btn-danger btn-md delete" data-id="{{ $webhook_obj->id }}" style="float:left;"><span class="fa fa-trash"></span></button>
                </div>
                <div class="process-result col-sm-3 px-1 mt-10"></div>
            </div>
            @endif

        </div>
    </div>

@endforeach