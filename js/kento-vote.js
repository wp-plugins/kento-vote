jQuery(document).ready(function(){var e=jQuery("#kento-vote").attr("logged");if(e=="notlooged"){jQuery("#kento-vote-up, #kento-vote-down").click(function(){jQuery("#loginform").css("display","block");jQuery(".login-bg").css("display","block")});jQuery(".login-bg").click(function(){jQuery("#loginform").hide();jQuery(this).hide()})}jQuery("#kento-vote-up, #kento-vote-down").click(function(){var e=jQuery("#kento-vote").attr("class");if(e=="voted"){jQuery("#kento-vote-up .up-vote-text").html(" You already voted");jQuery("#kento-vote-down .down-vote-text").html(" You already voted")}else if(e=="notvoted"){jQuery("#kento-vote").attr("class","voted");var t=jQuery(this).attr("postid");var n=jQuery(this).attr("votetype");jQuery.ajax({type:"POST",url:MyAjax.ajaxurl,data:{action:"kento_vote_insert",postid:t,votetype:n},success:function(e){if(n=="upvote"){var t=parseInt(jQuery("span.up-vote-value").attr("upvotevalue"))+1;jQuery("span.up-vote-value").text(t);jQuery("#kento-vote-up .up-vote-text").html(" Thanks for vote!")}else if(n=="downvote"){var r=parseInt(jQuery("span.down-vote-value").attr("downvotevalue"))+1;jQuery("span.down-vote-value").text(r);jQuery("#kento-vote-down .down-vote-text").html(" Thanks for vote!")}}})}})})