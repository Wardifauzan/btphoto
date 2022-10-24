jQuery(function ($) {

    // Create tinymce plugin
    tinymce.PluginManager.add('sdmSqueezeMCE', function (ed, url) {

	ed.addButton('sdmSqueezeMCE', {

	    title: 'SDM Squeeze Forms',
	    image: url + '/sdm_squeeze_forms.png',
	    onclick: function () {

		ed.windowManager.open({

		    title: 'Insert SDM Squeeze Form',
		    body: [
			{
			    type: 'listbox',
			    name: 'sdm_squeeze_select_item',
			    label: 'Select Download Item:',
			    'values': sdm_squeeze_item_ids  // Taken from variable passed via main.php file
			},
			{
			    type: 'listbox',
			    name: 'sdm_squeeze_fancy_option',
			    label: 'Select Fancy Style:',
			    'values': [
				{text: '0', value: '0'},
				{text: '1', value: '1'},
                {text: '2', value: '2'},
				{text: '3', value: '3'},
				{text: '4', value: '4'}
			    ]
			},
			{
			    type: 'textbox',
			    name: 'sdm_squeeze_button_text',
			    label: 'Adjust Button Text:',
			    value: 'Download Now'
			}
		    ],
		    onsubmit: function (e) {

			if (e.data.sdm_squeeze_select_item === '') {

			    e.preventDefault();
			    alert('A Download Item must be selected.');
			    return;
			}

			ed.insertContent('[sdm-squeeze-form id="' + e.data.sdm_squeeze_select_item + '" fancy="' + e.data.sdm_squeeze_fancy_option + '" button_text="' + e.data.sdm_squeeze_button_text + '"]');
		    }
		});
	    }
	});
    });
});