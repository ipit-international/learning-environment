###

ownCloud - App Framework

@author Bernhard Posselt
@copyright 2012 Bernhard Posselt dev@bernhard-posselt.com

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library.  If not, see <http://www.gnu.org/licenses/>.

###


# Removes html tags
# Example: remove all html tags {{ variable|ocRemoveTags:''}}
# Example: remove all <em> tags {{ variable|ocRemoveTags:'em'}}
# Example: remove all <em> and <p> tags {{ variable|ocRemoveTags:['em', 'p']}}
angular.module('OC').filter 'ocRemoveTags', ->

	return (input, tagsToRemove=null) ->
		if angular.isArray(tagsToRemove)
			for tag in tagsToRemove
				replaceRegex = '(</?' + tag + '[^>]*>)'
				regex = new RegExp(replaceRegex, 'ig')
				input = input.replace(regex, '')

		else if tagsToRemove != null
			replaceRegex = '(</?' + tagsToRemove + '[^>]*>)'
			regex = new RegExp(replaceRegex, 'ig')
			input = input.replace(regex, '')

		return input
		
