
@extends('layouts.adminox')


@section('content')
<script type="text/javascript" language="javascript">
<!--
// delete action confirm
function action_onclick(cgi_url,form_name,radio_name,action,ziduan)
{
	if(ziduan==null || ziduan==undefined || ziduan=="undefined" || ziduan=="") ziduan = "id";

	len = form_name.elements.length;

	for(index=0; index < len; index++ )
	{
		if(form_name.elements[index].name == radio_name)
		{
			var obj = form_name.elements[radio_name];
			var len = obj.length;
			var	check;
			var check_count = 0;
			var radio_id;
			if(len != null)
			{
				for(var i=0;i<len;i++)
				{
					check = obj[i].checked;
					if(check == true)
					{
						radio_id = obj[i].value;
						check_count++;
					}
				}
			}
			else
			{
				check = obj.checked;
				if(check == true)
					{
						radio_id = obj.value;
						check_count++;
					}
			}
		}
	}
	if(check_count == 1)
	{
		if(action == "del")
		{
			if(!confirm("<!--{$tpl_qingquerenshifouzhendeshanchu}-->?"))
			{
				return false;
			}
			form_name.elements["do"].value="<!--{$type_name}-->_edit";
			form_name.elements["action"].value="edit";
			form_name.elements["status_"].value="del";
			form_name.submit();
			return true;
		}
		cgi_url = cgi_url + "&action=" + action + "&" + ziduan + "=" + radio_id;
		window.self.open(cgi_url,"_self");
		return true;
	}
	else
	{
		alert("<!--{$tpl_qingninxuanzhongyitiaoxinxi}-->!");
		return false;
	}
}
function PlusQueryClick(a_id){
	if(document.getElementById(a_id).style.display == "block"){
		document.getElementById(a_id).style.display = "none";
	}else{
		document.getElementById(a_id).style.display = "block";
	}
}
-->
</script>



@endsection