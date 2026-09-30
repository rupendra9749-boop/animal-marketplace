{{-- Public "how to delete your account" page (Google Play requires a web link for this). --}}
<x-app-layout>
    <x-slot name="title">{{ __('Delete your account') }}</x-slot>

    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-5 text-stone-700 leading-relaxed [&_h2]:text-lg [&_h2]:font-extrabold [&_h2]:text-stone-900 [&_h2]:mt-8 [&_ol]:list-decimal [&_ol]:pl-6 [&_ul]:list-disc [&_ul]:pl-6 [&_li]:mt-1">
        <h1 class="text-3xl font-extrabold text-stone-900">{{ __('Delete your account') }}</h1>

        @if (app()->getLocale() === 'hi')
            <p>आप अपना AnimalMandi खाता और उससे जुड़ी जानकारी खुद, कभी भी हटा सकते हैं।</p>
            <h2>कैसे हटाएँ</h2>
            <ol>
                <li>ऐप या वेबसाइट में लॉग इन करें।</li>
                <li>मेनू में <strong>प्रोफ़ाइल</strong> खोलें।</li>
                <li>नीचे <strong>खाता हटाएँ</strong> दबाएँ और अपना पासवर्ड डालकर पक्का करें।</li>
            </ol>
            <h2>क्या हटता है</h2>
            <ul>
                <li>आपका खाता (नाम, ईमेल, मोबाइल नंबर, शहर)।</li>
                <li>आपकी पशु लिस्टिंग और फ़ोटो, आपकी चैट, और आपके खरीदे ऑर्डर।</li>
                <li>आपकी डॉक्टर और केयरटेकर प्रोफ़ाइल, और आपकी डाली हुई सभी फ़ोटो।</li>
            </ul>
            <p>लॉग इन नहीं कर पा रहे? <a class="text-amber-700 font-semibold underline" href="{{ route('contact') }}">संपर्क पेज</a> से अपने खाते का ईमेल या मोबाइल नंबर लिखकर हटाने को कहें। हम 7 दिन के अंदर खाता हटा देंगे।</p>
        @else
            <p>You can delete your AnimalMandi account and its data yourself, at any time.</p>
            <h2>How to delete it</h2>
            <ol>
                <li>Log in to the app or the website.</li>
                <li>Open <strong>Profile</strong> from the menu.</li>
                <li>Tap <strong>Delete Account</strong> at the bottom and confirm with your password.</li>
            </ol>
            <h2>What is deleted</h2>
            <ul>
                <li>Your account (name, email, mobile number, city).</li>
                <li>Your animal listings and photos, your chats, and the orders you placed.</li>
                <li>Your doctor and caretaker profiles, and every photo you uploaded.</li>
            </ul>
            <p>Can't log in? Ask for deletion from the <a class="text-amber-700 font-semibold underline" href="{{ route('contact') }}">contact page</a> with your account email or mobile number. We delete the account within 7 days.</p>
        @endif

        @auth
            <a href="{{ route('profile.edit') }}" class="inline-block mt-4 px-5 py-3 rounded-xl bg-red-600 text-white font-bold">{{ __('Go to my profile to delete') }}</a>
        @endauth
    </article>
</x-app-layout>
