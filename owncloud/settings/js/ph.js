$(document).ready(function(){
	if(!Modernizr.input.placeholder){
		$('#pass1[placeholder]').focus(function() {
		  var input = $(this);
		  if (input.val() == input.attr('placeholder')) {
			input.val('');
			input.data('type','text').get(0).type='password'
			input.removeClass('placeholder');
		  }
		}).blur(function() {
		  var input = $(this);
		  if (input.val() == '' || input.val() == input.attr('placeholder')) {
			input.data('type','password').get(0).type='text'
			input.addClass('placeholder');
			input.val(input.attr('placeholder'));
		  }
		}).blur();
		
		$('#pass1[placeholder]').parents('form').submit(function() {
		  $(this).find('#pass1[placeholder]').each(function() {
			var input = $(this);
			if (input.val() == input.attr('placeholder')) {
			  input.val('');
			}
		  })
		});
	
	//======================	^PASS1^		==========================	
	
		$('#pass2[placeholder]').focus(function() {
		  var input = $(this);
		  if (input.val() == input.attr('placeholder')) {
			input.val('');
			input.data('type','text').get(0).type='password'
			input.removeClass('placeholder');
		  }
		}).blur(function() {
		  var input = $(this);
		  if (input.val() == '' || input.val() == input.attr('placeholder')) {
			input.data('type','password').get(0).type='text'
			input.addClass('placeholder');
			input.val(input.attr('placeholder'));
		  }
		}).blur();
		
		$('#pass2[placeholder]').parents('form').submit(function() {
		  $(this).find('#pass2[placeholder]').each(function() {
			var input = $(this);
			if (input.val() == input.attr('placeholder')) {
			  input.val('');
			}
		  })
		});

	//======================	^PASS2^		==========================	

		$('#pass3[placeholder]').focus(function() {
		  var input = $(this);
		  if (input.val() == input.attr('placeholder')) {
			input.val('');
			input.data('type','text').get(0).type='password'
			input.removeClass('placeholder');
		  }
		}).blur(function() {
		  var input = $(this);
		  if (input.val() == '' || input.val() == input.attr('placeholder')) {
			input.data('type','password').get(0).type='text'
			input.addClass('placeholder');
			input.val(input.attr('placeholder'));
		  }
		}).blur();
		
		$('#pass3[placeholder]').parents('form').submit(function() {
		  $(this).find('#pass2[placeholder]').each(function() {
			var input = $(this);
			if (input.val() == input.attr('placeholder')) {
			  input.val('');
			}
		  })
		});

	//======================	^PASS3^		==========================	
	
		$('#newuser [placeholder]').focus(function() {
		  var input = $(this);
		  if (input.val() == input.attr('placeholder')) {
			input.val('');
			input.removeClass('placeholder');
		  }
		}).blur(function() {
		  var input = $(this);
		  if (input.val() == '' || input.val() == input.attr('placeholder')) {
			input.addClass('placeholder');
			input.val(input.attr('placeholder'));
		  }
		}).blur();
		
		$('#newuser [placeholder]').parents('form').submit(function() {
		  $(this).find('#newuser [placeholder]').each(function() {
			var input = $(this);
			if (input.val() == input.attr('placeholder')) {
			  input.val('');
			}
		  })
		});

	//======================	^NEWUS^		==========================	
	
		$('#new_group [placeholder]').focus(function() {
		  var input = $(this);
		  if (input.val() == input.attr('placeholder')) {
			input.val('');
			input.removeClass('placeholder');
		  }
		}).blur(function() {
		  var input = $(this);
		  if (input.val() == '' || input.val() == input.attr('placeholder')) {
			input.addClass('placeholder');
			input.val(input.attr('placeholder'));
		  }
		}).blur();
		
		$('#new_group [placeholder]').parents('form').submit(function() {
		  $(this).find('#new_group [placeholder]').each(function() {
			var input = $(this);
			if (input.val() == input.attr('placeholder')) {
			  input.val('');
			}
		  })
		});

	//======================	^NEWGR^		==========================	

	}
});
