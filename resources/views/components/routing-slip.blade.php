<div class="bg-pink-200 p-6 max-w-md w-full border border-gray-800" style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
    <div class="text-center mb-4">
        <div class="flex justify-end mb-2">  <!-- Changed from justify-start to justify-end -->
            <button @click="open = false" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <p class="text-xs font-bold">REPUBLIC OF THE PHILIPPINES</p>
        <h1 class="text-lg font-bold mt-1">ZAMBOANGA PENINSULA POLYTECHNIC STATE UNIVERSITY</h1>
        <p class="text-xs">R.T. LIM BLVD., ZAMBOANGA CITY</p>
        <div class="border-t-2 border-b-2 border-black py-1 my-2">
            <p class="font-bold text-sm">{{ $recipient }}</p>
        </div>
    </div>

    <div class="mb-4">
        <div class="flex justify-between mb-3">
            <p class="text-sm"><span class="font-bold">DATE:</span> {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</p>
        </div>
        
        {{-- <p class="text-sm mb-3">Respectfully returning/forwarding the attached letter/documents, for the following:</p>
        
        <div class="ml-6 mb-3">
            <div class="flex items-start mb-1">
                <div class="w-3 h-3 border border-black mr-2 mt-1"></div>
                <p class="text-sm">For information</p>
            </div>
            <div class="flex items-start mb-1">
                <div class="w-3 h-3 border border-black mr-2 mt-1"></div>
                <p class="text-sm">For appropriate action</p>
            </div>
            <div class="flex items-start">
                <div class="w-3 h-3 border border-black mr-2 mt-1"></div>
                <p class="text-sm">Comments:</p>
            </div>
        </div> --}}
        
        <div class="mb-3">
            <p class="font-bold text-sm mb-1">Remarks:</p>
            <div class="border-b border-black h-8"><p class="text-sm mb-2">{{ $remarks }}</p></div>
        </div>
        
        {{-- <div class="mb-4">
            <p class="font-bold text-sm mb-1">Others:</p>
            <div class="border-b border-black h-8"></div>
        </div>
        
        <p class="text-sm mb-2">Please return and give your feedback</p> --}}
    </div>

    <div class="text-center mt-6">
        <img class="w-20 rounded-full object-cover mx-auto" 
            src="{{ asset('storage/assets/img/fakesig1.png') }}">
        <div class="inline-block border-t border-black px-8">
            <p class="font-bold text-sm">{{ $head }}</p>
        </div>
    </div>

    {{-- <!-- Action Buttons -->
    <div class="mt-6 flex justify-end space-x-2">
        <button @click="open = false" class="px-3 py-1 text-xs border border-gray-400 hover:bg-gray-100">Close</button>
        <button class="px-3 py-1 bg-blue-600 text-white text-xs hover:bg-blue-700">Submit</button>
    </div> --}}
</div>