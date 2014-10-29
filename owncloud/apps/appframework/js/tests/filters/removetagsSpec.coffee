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

describe 'ocRemoveTags', ->

	beforeEach module 'OC'

	beforeEach inject ($filter) =>
		@filter = $filter('ocRemoveTags')


	it 'should not remove tags if nothing is passed', =>
		sentence = 'this <em> is a problem</em>'
		out = @filter(sentence)

		expect(out).toBe(sentence)


	it 'should not remove tags that are not passed', =>
		sentence = 'this <em> is a problem</em>'
		out = @filter(sentence, 'p')

		expect(out).toBe(sentence)


	it 'should remove element if one tag is passed', =>
		sentence = 'this <em> is a problem</em>'
		filtered = 'this  is a problem'
		out = @filter(sentence, 'em')

		expect(out).toBe(filtered)


	it 'should remove multiple elements if an array with tags is passed', =>
		sentence = 'this <em> is a<br/> problem</em>'
		filtered = 'this  is a problem'
		remove = ['em', 'p', 'br']
		out = @filter(sentence, remove)

		expect(out).toBe(filtered)


	it 'should not remove tags that are not passed', =>
		sentence = 'this <em> is a<br/> problem</em>'
		filtered = 'this  is a<br/> problem'
		remove = ['em', 'p']
		out = @filter(sentence, remove)

		expect(out).toBe(filtered)


	it 'should remove all tags if an empty string is passed', =>
		sentence = 'this <em> is a<br/> problem</em>'
		filtered = 'this  is a problem'
		remove = ''
		out = @filter(sentence, remove)

		expect(out).toBe(filtered)