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

describe 'ocSanitizeURL', ->

	beforeEach module 'OC'

	beforeEach inject ($filter) =>
		@filter = $filter('ocSanitizeURL')


	it 'should return null if xss', =>
		url = 'javascript:alert(\'hi\')'
		out = @filter(url)

		expect(out).toBe('')

	it 'should return null if xss case insensitive', =>
		url = 'JaVaScRiPt:alert(\'hi\')'
		out = @filter(url)

		expect(out).toBe('')


	it 'should return null if xss spaces', =>
		url = 'java	script:alert(\'hi\')'
		out = @filter(url)

		expect(out).toBe('')


	it 'should return null if xss encoded tab', =>
		url = 'jav&#x09;ascript:alert(\'XSS\');'
		out = @filter(url)

		expect(out).toBe('')


	it 'should return null if xss newline', =>
		url = 'jav&#x0A;ascript:alert(\'XSS\');'
		out = @filter(url)

		expect(out).toBe('')


	it 'should return null if xss carriage return', =>
		url = 'jav&#x0D;ascript:alert(\'XSS\');'
		out = @filter(url)

		expect(out).toBe('')


	it 'should return null if xss backticks', =>
		url = '`javascript:alert("RSnake says, \'XSS\'")`'
		out = @filter(url)

		expect(out).toBe('')


	it 'should return null if xss utf-8', =>
		url = '&#106;&#97;&#118;&#97;&#115;&#99;&#114;' +
			'&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;' +
			'&#39;&#88;&#83;&#83;&#39;&#41;'
		out = @filter(url)

		expect(out).toBe('')
		

	it 'should return null if xss utf-8 long', =>
		url = '&#0000106&#0000097&#0000118&#0000097&' +
			'#0000115&#0000099&#0000114&#0000105&#0000112&' +
			'#0000116&#0000058&#0000097&#0000108&#0000101&' +
			'#0000114&#0000116&#0000040&#0000039&#0000088&' +
			'#0000083&#0000083&#0000039&#0000041'
		out = @filter(url)

		expect(out).toBe('')


	it 'should validate a www url', =>
		url = 'www.google.de'
		out = @filter(url)

		expect(out).toBe(url)


	it 'should validate an url', =>
		url = 'google.de'
		out = @filter(url)

		expect(out).toBe(url)


	it 'should validate an url', =>
		url = 'google.de/something#XML=123'
		out = @filter(url)

		expect(out).toBe(url)


	it 'should return null if xss null', =>
		url = 'java\0script:alert(\"XSS\")'
		out = @filter(url)

		expect(out).toBe('')


	it 'should return null if xss spaces meta', =>
		url = ' &#14;  javascript:alert(\'XSS\');;'
		out = @filter(url)

		expect(out).toBe('')


	it 'should return null if xss protocol resolution', =>
		url = '//ha.ckers.org/.j'
		out = @filter(url)

		expect(out).toBe('')


	it 'should return null if xss js escapes', =>
		url = '\";alert(\'XSS\');//'
		out = @filter(url)

		expect(out).toBe('')


	it 'should accept an amazon url', =>
		url = 'http://www.amazon.com/Sony-Unlocked-Android-Phone--' +
			'U-S-Warranty/dp/B00C7ATZMK/ref=xs_gb_rss_4015761/' +
			'?ccmID=380205&tag=rssfeeds-20'
		out = @filter(url)

		expect(out).toBe(url)
		
