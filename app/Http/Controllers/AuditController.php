<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditTrail;

class AuditController extends Controller
{
    public function get_trails() {
        $trails = AuditTrail::orderBy('date_added', 'desc')->with('actor:id,first_name,last_name')->get();

        $t = $trails->map(function ($trail) {
            $array = [];
            $array['id'] = $trail->id;
            $array['action'] = $trail->action;
            $array['object_name'] = $trail->object_name;
            $array['date'] = $trail->date_added;
            if (!$trail->actor) {
                $array['actor'] = ['first_name'=> 'deleted', 'last_name'=> 'deleted'];
                $array['actor']['type'] = str($trail->actor_type)->contains('Student') ? 'student' : 'admin';
            } else {
                $array['actor'] = $trail->actor;
                $array['actor']['type'] = $trail->actor_type;
                
            }
            return $array;
        });
        
          
        return response()->json(['trails'=>$t], 200);
    }

    public function get(string $id) {
        $trail = AuditTrail::find($id);

        if (!$trail) return response()->json(['status'=>'failed', 'message'=>'Trail with provided ID not found'], 200);

        $trail->load('actor:id,first_name,last_name');

        $data = ['actor'=>$trail->actor, 'date'=>$trail->date_added, 'object_name'=>$trail->object_name, 'action'=>$trail->action];

        if ($trail->action == 'updated') {
            $patched_object = $this->getPatchedCourse(['from'=> $trail->object_from, 'to'=>$trail->object_to]);

            $data = [...$data, 'status'=> 'success', 'object' => $patched_object];
        } else {
            $object = $trail->object_record;

            $data = [...$data, 'status'=> 'success', 'object' => $object];
        }

        return response()->json($data, 200);
    }

    private function getPatchedCourse($trail) {
        $old = $trail['from']->toArray();
        $new = $trail['to']->toArray();


        function createHash($value) {
            if (!is_string($value)) $value = json_encode($value);

            return hash('sha256', $value);
        }


        $patched = []; //array containg data plus information of changes that occured between two versions

        $has_children = ['attendees', 'prerequisites', 'modules', 'objectives']; //attributes that are not scalar but have children

        $exclude = ['parent_id', 'date_added']; //attributes that are not original to model

        foreach($new as $attribute => $value) {

            if (in_array($attribute, $exclude)) continue;

            $old_value = $old[$attribute];
            $new_value = $value;


            if ($unchanged = hash_equals(createHash($old_value), createHash($new_value))) {

                //attribute is unchanged across both versions
                $patched[$attribute]['changed'] = false;
                $patched[$attribute]['value'] = $new_value;

                continue;
            } else {

                //attribute is different accross both versions
                $patched[$attribute]['changed'] = true;

                if (in_array($attribute, $has_children)) { //attribute has children and each is checked for change
                    $old_children = $old_value;
                    $new_children = $new_value;

                    $patched[$attribute]['has_children'] = true;

                    if ($attribute == 'modules') {
                        //check for changes in each modules objective or overview

                        $new_modules = $new_children;
                        $old_modules = $old_children;
                        $max_count = max(count($new_modules), count($old_modules));

                        function compareModuleAttributes($old_value, $new_value, $attribute_name, &$module) {
                            if ($old_value == $new_value) {
                                $module[$attribute_name]['changed'] = false;
                                $module[$attribute_name]['value'] = $new_value;
                            } else {
                                $module[$attribute_name]['changed'] = true;
                                $module[$attribute_name]['new'] = $new_value;
                                $module[$attribute_name]['old'] = $old_value;
                            }
                        }

                        
                        for ($i = 0; $i < $max_count; $i++) {

                            $new_module = isset($new_modules[$i]) ? $new_modules[$i] : null;
                            $old_module = isset($old_modules[$i]) ? $old_modules[$i] : null;

                            if ($new_module && $old_module) {
                                compareModuleAttributes($new_module->objective, $old_module->objective, 'objective', $patched[$attribute][$i]);

                                compareModuleAttributes($new_module->overview, $old_module->overview, 'overview', $patched[$attribute][$i]);
                            } else if ($new_module == null) {
                                $patched[$attribute][$i]['objective']['changed'] = true;
                                $patched[$attribute][$i]['objective']['old'] = $old_module->objective;
                                $patched[$attribute][$i]['overview']['changed'] = true;
                                $patched[$attribute][$i]['overview']['old'] = $old_module->overview;
                            } else if ($old_module == null) {
                                $patched[$attribute][$i]['objective']['changed'] = true;
                                $patched[$attribute][$i]['objective']['new'] = $new_module->objective;
                                $patched[$attribute][$i]['overview']['changed'] = true;
                                $patched[$attribute][$i]['overview']['new'] = $new_module->overview;
                            }
                        }

                    } else {
                        $max_count = max(count($new_children), count($old_children));

                        for ($i = 0; $i < $max_count; $i++) {
                            $new_value = isset($new_children[$i]) ? $new_children[$i] : null;
                            $old_value = isset($old_children[$i]) ? $old_children[$i] : null;

                            if ($new_value !== null && $old_value !== null) {
                                $unchanged = hash_equals(createHash($old_value), createHash($new_value));
                                $patched[$attribute][$i] = $unchanged ?
                                ['changed'=>false, 'value'=>$new_value] :
                                ['changed'=>true, 'old'=>$old_value, 'new'=>$new_value];
                            } elseif ($new_value !== null) {
                                $patched[$attribute][$i] = [
                                    'changed' => true,
                                    'new' => $new_value,
                                ];
                            } elseif ($old_value !== null) {
                                $patched[$attribute][$i] = [
                                    'changed' => true,
                                    'old' => $old_value,
                                ];
                            }
                        }
                    }
                } else { //attribute is scalar
                    $patched[$attribute]['old'] = $old_value;
                    $patched[$attribute]['new'] = $new_value;
                }

                continue;

            }
        }

        return $patched;
    }
}
