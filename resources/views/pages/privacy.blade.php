{{-- Privacy policy (linked from Google Play). Written out in both languages instead of going through __() line by line. --}}
<x-app-layout>
    <x-slot name="title">{{ __('Privacy policy') }}</x-slot>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6 text-stone-700 leading-relaxed [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:text-stone-900 [&_h2]:mt-8 [&_ul]:list-disc [&_ul]:pl-6 [&_li]:mt-1">
        <h1 class="text-3xl font-extrabold text-stone-900">{{ __('Privacy policy') }}</h1>
        <p class="text-sm text-stone-500">{{ __('Last updated: :date', ['date' => '28 September 2026']) }}</p>

        @if (app()->getLocale() === 'hi')
            <p>यह नीति बताती है कि AnimalMandi (वेबसाइट animalmandi.wuaze.com और Android ऐप) आपकी कौन-सी जानकारी लेता है, क्यों लेता है और आप उसे कैसे हटा सकते हैं।</p>

            <h2>हम कौन-सी जानकारी लेते हैं</h2>
            <ul>
                <li><strong>खाते की जानकारी:</strong> नाम, ईमेल, मोबाइल नंबर, राज्य और शहर, पासवर्ड (सुरक्षित रूप में, कभी सादे रूप में नहीं)।</li>
                <li><strong>आप जो डालते हैं:</strong> पशुओं की लिस्टिंग और फ़ोटो, डॉक्टर या केयरटेकर प्रोफ़ाइल, ऑर्डर, डिलीवरी का पता, चैट संदेश, संपर्क फ़ॉर्म के संदेश।</li>
                <li><strong>लोकेशन:</strong> सिर्फ़ तब, जब आप "मेरी लोकेशन इस्तेमाल करें" दबाते हैं। इससे हम आपका सबसे पास का शहर ढूँढते हैं। हम आपकी लोकेशन लगातार ट्रैक नहीं करते और GPS निर्देशांक सेव नहीं करते।</li>
                <li><strong>तकनीकी जानकारी:</strong> लॉग इन रखने के लिए कुकीज़, और सर्वर लॉग (जैसे IP पता) जो सुरक्षा और गड़बड़ी ठीक करने के लिए होते हैं।</li>
            </ul>

            <h2>हम इसका इस्तेमाल कैसे करते हैं</h2>
            <ul>
                <li>आपके पास के पशु, डॉक्टर और केयरटेकर दिखाने के लिए।</li>
                <li>ऑर्डर पूरा करने के लिए: ऑर्डर होने पर खरीदार को विक्रेता का नाम, फ़ोन, ईमेल और शहर दिखता है, और विक्रेता को खरीदार की जानकारी और डिलीवरी का पता।</li>
                <li>डॉक्टर और केयरटेकर प्रोफ़ाइल सार्वजनिक होती हैं, ताकि लोग उन्हें कॉल कर सकें।</li>
                <li>ऑर्डर से जुड़े ईमेल भेजने और आपके सवालों का जवाब देने के लिए।</li>
            </ul>
            <p>हम आपकी जानकारी बेचते नहीं हैं और विज्ञापन के लिए किसी को नहीं देते। ऐप में कोई विज्ञापन या ट्रैकिंग टूल नहीं है।</p>

            <h2>जानकारी कहाँ रखी जाती है</h2>
            <p>जानकारी हमारे होस्टिंग सर्वर पर रखी जाती है और वेबसाइट व ऐप के बीच HTTPS (एन्क्रिप्टेड कनेक्शन) से भेजी जाती है।</p>

            <h2>खाता और जानकारी हटाना</h2>
            <p>आप कभी भी अपना खाता हटा सकते हैं: लॉग इन करें → प्रोफ़ाइल → "खाता हटाएँ"। इससे आपका खाता, लिस्टिंग, फ़ोटो, डॉक्टर/केयरटेकर प्रोफ़ाइल, विशलिस्ट, चैट और ऑर्डर हट जाते हैं। पूरी जानकारी यहाँ है: <a class="text-amber-700 font-semibold underline" href="{{ route('account.deletion') }}">{{ route('account.deletion') }}</a></p>

            <h2>बच्चे</h2>
            <p>AnimalMandi 18 साल से कम उम्र के बच्चों के लिए नहीं है।</p>

            <h2>संपर्क</h2>
            <p>इस नीति के बारे में कोई सवाल हो तो <a class="text-amber-700 font-semibold underline" href="{{ route('contact') }}">संपर्क पेज</a> से हमें लिखें।</p>
        @else
            <p>This policy explains what information AnimalMandi (the website animalmandi.wuaze.com and the Android app) collects, why, and how you can delete it.</p>

            <h2>What we collect</h2>
            <ul>
                <li><strong>Account details:</strong> name, email, mobile number, state and city, and your password (stored securely, never in plain text).</li>
                <li><strong>What you add:</strong> animal listings and photos, doctor or caretaker profiles, orders, delivery address, chat messages, and contact-form messages.</li>
                <li><strong>Location:</strong> only when you tap "Use my current location". We use it once to find your nearest city. We do not track your location and do not store GPS coordinates.</li>
                <li><strong>Technical data:</strong> cookies that keep you logged in, and server logs (such as IP address) used for security and fixing problems.</li>
            </ul>

            <h2>How we use it</h2>
            <ul>
                <li>To show animals, doctors and caretakers near you.</li>
                <li>To complete orders: after an order, the buyer sees the seller's name, phone, email and city, and the seller sees the buyer's details and delivery address.</li>
                <li>Doctor and caretaker profiles are public so that people can call them.</li>
                <li>To send order emails and answer your questions.</li>
            </ul>
            <p>We do not sell your information or share it for advertising. The app has no ads and no tracking tools.</p>

            <h2>Where it is stored</h2>
            <p>Data is stored on our hosting server and sent between the website and the app over HTTPS (an encrypted connection).</p>

            <h2>Deleting your account and data</h2>
            <p>You can delete your account at any time: log in → Profile → "Delete Account". This removes your account, listings, photos, doctor and caretaker profiles, wishlist, chats and orders. Full details: <a class="text-amber-700 font-semibold underline" href="{{ route('account.deletion') }}">{{ route('account.deletion') }}</a></p>

            <h2>Children</h2>
            <p>AnimalMandi is not meant for children under 18.</p>

            <h2>Contact</h2>
            <p>Questions about this policy? Write to us from the <a class="text-amber-700 font-semibold underline" href="{{ route('contact') }}">contact page</a>.</p>
        @endif
    </article>
</x-app-layout>
