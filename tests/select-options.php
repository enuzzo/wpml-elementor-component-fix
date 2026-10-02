<?php
// SPDX-License-Identifier: GPL-2.0-or-later
// Original synthetic fixtures; no site identifiers, exports or vendor code.
require __DIR__.'/bootstrap.php';
$mode=$argv[1];
$GLOBALS['select_mode']=$mode;
if ($mode==='typed') {
    eval('namespace WPML\\PB\\Elementor\\Modules; class ModuleWithItemsFromConfig { public function __construct($collection, array $fields) {} public function get($id,$element,$strings): array { return []; } }');
} elseif ($mode!=='absent') {
    require __DIR__.'/select-items-double.php';
}
if ($mode==='combined') {
    $GLOBALS['native_mode']='broken';
    require __DIR__.'/native-double.php';
    $GLOBALS['master_mode']='broken';
    require __DIR__.'/master-nodes-double.php';
    $GLOBALS['master_base_config']=[];
}
function select_config() {
    return ['e-form-select'=>['conditions'=>['widgetType'=>'e-form-select'],
        'fields'=>[['field'=>'label>value','type'=>'Existing label','editor_type'=>'LINE']],
        'fields_in_item'=>['options>value'=>[['field'=>'value>key>value','type'=>'Synthetic option','editor_type'=>'LINE']]],
    ]];
}
function select_node() {
    $rows=[];
    foreach (['stable-a'=>'Same label','stable-b'=>'Different & café','stable-c'=>'Same label','stable-zero'=>'0','stable-empty'=>''] as $value=>$label) {
        $rows[]=['$$type'=>'key-value','value'=>[
            'key'=>['$$type'=>'string','value'=>$label],
            'value'=>['$$type'=>'string','value'=>$value],
        ],'preserve'=>['flag'=>true]];
    }
    return ['id'=>'invented-select','widgetType'=>'e-form-select','settings'=>[
        'options'=>['$$type'=>'options','value'=>$rows,'preserve'=>'collection'],
        'label'=>['$$type'=>'string','value'=>'Existing control'],
    ]];
}
function import_option($module,$node,$translation) {
    list($key,$item)=$module->update($node['id'],$node,$translation);
    if ($key!==null && $item!==null) {
        $leaf=&$node['settings'];
        foreach (explode('>',$module->get_items_field()) as $part) { $leaf=&$leaf[$part]; }
        $leaf[$key]=$item;
        unset($leaf);
    }
    return $node;
}
load_plugin();
$filter=$GLOBALS['adapter_filter'];
$original=select_config();
$mapped=$filter($original);
if (in_array($mode,['typed','absent'],true)) {
    check($mapped===$original,'Unknown/absent contract left unchanged');
} elseif ($mode==='unknown') {
    $cases=[];
    foreach ([['integration-class'=>['Custom_Select']],['conditions'=>['widgetType'=>'different']],['extra'=>true],['fields_in_item'=>[]]] as $patch) {
        $copy=$original; $copy['e-form-select']=array_replace($copy['e-form-select'],$patch); $cases[]=$copy;
    }
    $field=$original['e-form-select']['fields_in_item']['options>value'][0];
    foreach ([array_merge($field,['field'=>'value>value>value']),array_merge($field,['field_id'=>'custom']),array_merge($field,['editor_type'=>'VISUAL']),array_merge($field,['type'=>null])] as $other) {
        $copy=$original; $copy['e-form-select']['fields_in_item']['options>value']=[$other]; $cases[]=$copy;
    }
    $copy=$original; $copy['e-form-select']['fields'][]=['field'=>'options>value','type'=>'Native options']; $cases[]=$copy;
    foreach($cases as $input) { check($filter($input)===$input,'Unknown/custom/existing option registration unchanged'); }
    check($filter(null)===null,'Non-array configuration unchanged');
} else {
    check($mapped['e-form-select']['fields']===$original['e-form-select']['fields'],'Ordinary fields preserved');
    check(!isset($mapped['e-form-select']['fields_in_item']),'Original collection routed once through adapter');
    check($mapped['e-form-select']['integration-class']===['Netmilk_WPML_Select_Options'],'Scoped integration registered');
    check($filter($mapped)===$mapped,'Registration idempotent');
    $module=new Netmilk_WPML_Select_Options();
    $node=select_node(); $snapshot=$node;
    $existing=[new WPML_PB_String('Existing','other-field','Control','LINE')];
    $handler_before=set_error_handler(function(){}); restore_error_handler();
    $strings=$module->get($node['id'],$node,$existing);
    $handler_after=set_error_handler(function(){}); restore_error_handler();
    check($handler_after===$handler_before,'Previous error handler restored after native probe');
    if (in_array($mode,['throws','unexpected_warning','collision'],true)) {
        check($strings===$existing,'Unknown failure/colliding identities append nothing');
        if ($mode!=='collision') {
            $translation=new WPML_PB_String('New','unknown','','LINE');
            check($module->update($node['id'],$node,$translation)===[null,null],'Unknown runtime failure yields no update');
        } else {
            $translation=new WPML_PB_String('Must not choose a row','synthetic-collision','','LINE');
            check($module->update($node['id'],$node,$translation)===[null,null],'Colliding identities rejected during import as well');
        }
        if ($mode==='unexpected_warning') {
            $GLOBALS['select_mode']='broken';
            check(count($module->get($node['id'],$node,[]))===4,'Unknown warning does not cache a broken support decision');
        }
    } else {
        check(count($strings)===5 && $strings[0]===$existing[0],'Four visible options plus incoming string');
        $options=array_slice($strings,1);
        check(array_map(function($s){return $s->get_value();},$options)===['Same label','Different & café','Same label','0'],'Duplicates and exact zero label exported; blank omitted');
        check(count(array_unique(array_map(function($s){return $s->get_name();},$options)))===4,'Every option has a separate identity');
        foreach ($options as $string) { check($string->title==='Synthetic option' && $string->editor==='LINE','Native metadata retained'); }
        if($mode==='fixed') {
            $native=new \WPML\PB\Elementor\Modules\ModuleWithItemsFromConfig('options>value',$original['e-form-select']['fields_in_item']['options>value']);
            check($options==$native->get($node['id'],$node,[]),'Working native extraction used without adapter identities');
        } else {
            check($module->get($node['id'],$node,$strings)==$strings,'Repeat extraction adds no adapter duplicates');
        }
        foreach ($options as $index=>$string) {
            $target=$index===3 ? 'Zero translated' : 'Target '.$index.' <b>text</b> &amp; café';
            $translation=new WPML_PB_String($target,$string->get_name(),'','LINE');
            $result=import_option($module,$node,$translation);
            $expected=$node;
            if($mode!=='unsafe') { $expected['settings']['options']['value'][$index]['value']['key']['value']=$target; }
            check($result===$expected,'Only correct option label imported, technical values/types/metadata preserved');
            check(import_option($module,$result,$translation)===$result,'Repeated import idempotent');
        }
        if($mode!=='unsafe') {
            $zero_target=new WPML_PB_String('0',$options[1]->get_name(),'','LINE');
            $zero_result=import_option($module,$node,$zero_target);
            $zero_expected=$node; $zero_expected['settings']['options']['value'][1]['value']['key']['value']='0';
            check($zero_result===$zero_expected,'Literal zero target is preserved without a surrogate value');
            $reordered=$node;
            $reordered['settings']['options']['value']=array_reverse($reordered['settings']['options']['value']);
            $fresh=$module->get($node['id'],$reordered,[]);
            check(array_map(function($s){return $s->get_name();},$fresh)===array_reverse(array_map(function($s){return $s->get_name();},$options)),'Identities survive reordering including duplicate labels');
            $translation=new WPML_PB_String('Edited duplicate',$options[2]->get_name(),'','LINE');
            $expected=$reordered; $expected['settings']['options']['value'][2]['value']['key']['value']='Edited duplicate';
            check(import_option($module,$reordered,$translation)===$expected,'Import follows technical identity after reordering');
            $edited=$node; $edited['settings']['options']['value'][0]['value']['key']['value']='Second source cycle';
            $next=$module->get($node['id'],$edited,[]);
            check($next[0]->get_name()===$options[0]->get_name() && $next[0]->get_value()==='Second source cycle','Label edit keeps identity');
        }
        $foreign=new WPML_PB_String('Ignore','unknown-name','','LINE');
        check(import_option($module,$node,$foreign)===$node,'Unknown identity untouched');
        check($module->update($node['id'],$node,new WPML_PB_String([],$options[0]->get_name(),'','LINE'))===[null,null],'Non-string target rejected');
        if(!in_array($mode,['fixed','nested_only','import_broken'],true)) {
            foreach (['duplicate-value','empty-value','custom-id','malformed'] as $shape) {
                $unknown=$node;
                if($shape==='duplicate-value') { $unknown['settings']['options']['value'][1]['value']['value']['value']='stable-a'; }
                if($shape==='empty-value') { $unknown['settings']['options']['value'][0]['value']['value']['value']=''; }
                if($shape==='custom-id') { $unknown['settings']['options']['value'][0]['_id']='external'; }
                if($shape==='malformed') { $unknown['settings']['options']['value'][0]['value']['key']['value']=[]; }
                check($module->get($node['id'],$unknown,$existing)===$existing,'Ambiguous/malformed item contract not adapted');
            }
        }
    }
    check($node===$snapshot,'Source immutable, no persistent surrogate IDs or aliases');
    if($mode==='combined') {
        $input=array_merge($original,config(),['e-form'=>['fields'=>[['field'=>'form-name','type'=>'Synthetic name']]]]);
        $out=$filter($input);
        check($out['e-component']['integration-class'][0]==='Netmilk_WPML_Escaped_HTML_Overrides','Component adapter coexists');
        check($out['e-form']['fields'][1]['field_id']==='form-name','Form-name adapter coexists');
        check($out['e-form-select']['integration-class']===['Netmilk_WPML_Select_Options'],'Select adapter coexists');
        check($filter($out)===$out,'Combined registration idempotent');
    }
}
echo 'PASS select-'.$mode.': '.$GLOBALS['checks']." checks\n";
